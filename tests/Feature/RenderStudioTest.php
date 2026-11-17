<?php

use App\Jobs\RenderLoungePhotosJob;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Room;
use App\Models\Table;
use App\Models\User;
use App\Services\LoungeRenders;
use App\Services\MenuPhotoStore;
use App\Services\Render\DefaultRenders;
use App\Services\Render\Renderer;
use App\Services\Render\RenderStudio;
use Database\Seeders\DefaultTranslationsSeeder;
use Database\Seeders\MenuSeeder;
use Database\Seeders\TableSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

/**
 * Stands in for the headless browser: remembers what it was asked to shoot and
 * writes a small PNG where each screenshot would go.
 */
function fakeRenderer(): Renderer
{
    $renderer = new class implements Renderer
    {
        /** @var list<array{url: string, path: string, width: int, height: int}> */
        public array $shots = [];

        public function render(array $shots): void
        {
            foreach ($shots as $shot) {
                $this->shots[] = $shot;

                $image = imagecreatetruecolor(120, 90);
                imagefill($image, 0, 0, (int) imagecolorallocate($image, 40, 30, 90));
                imagepng($image, $shot['path']);
            }
        }
    };

    app()->instance(Renderer::class, $renderer);

    return $renderer;
}

function studioUser(string $role): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => $role, 'email_verified_at' => now()])->save();

    return $user;
}

function menuItemWithoutPhoto(string $name = 'Mint Tea'): MenuItem
{
    $category = MenuCategory::firstOrCreate(['sort_order' => 0], ['name' => ['en' => 'Tea & coffee', 'lv' => 'Tēja', 'ru' => 'Чай']]);

    return MenuItem::create([
        'menu_category_id' => $category->id,
        'name' => ['en' => $name, 'lv' => $name, 'ru' => $name],
        'description' => ['en' => 'A pot for two.', 'lv' => '', 'ru' => ''],
        'price' => 6,
        'sort_order' => 0,
    ]);
}

beforeEach(function () {
    Storage::fake('public');
    config(['site.render_studio' => true]);
    $this->seed(DefaultTranslationsSeeder::class);
});

// --- Lounge pictures ----------------------------------------------------------------------

test('render-lounge stores a picture for every room and table', function () {
    $this->seed(TableSeeder::class);
    $renderer = fakeRenderer();
    $room = Room::firstOrFail();
    $table = Table::firstOrFail();

    $this->artisan('perigee:render-lounge', ['--url' => 'http://lounge.test:8001'])
        ->expectsOutputToContain('Rendered 65 lounge pictures.')
        ->assertSuccessful();

    $disk = Storage::disk('public');
    expect($disk->files('renders/rooms'))->toHaveCount(25)
        ->and($disk->files('renders/tables'))->toHaveCount(40)
        ->and($disk->exists("renders/rooms/{$room->id}.webp"))->toBeTrue()
        ->and($disk->exists("renders/tables/{$table->id}.webp"))->toBeTrue()
        ->and(getimagesizefromstring((string) $disk->get("renders/tables/{$table->id}.webp"))['mime'] ?? null)->toBe('image/webp');

    expect($renderer->shots)->toHaveCount(65)
        ->and($renderer->shots[0]['url'])->toStartWith('http://lounge.test:8001/render-studio?')->toContain('scene=room', 'signature=')
        ->and([$renderer->shots[0]['width'], $renderer->shots[0]['height']])->toBe([1200, 800])
        ->and(File::exists(dirname($renderer->shots[0]['path'])))->toBeFalse();
});

test('render-lounge can render a single table', function () {
    $this->seed(TableSeeder::class);
    $renderer = fakeRenderer();
    $table = Table::where('name', 'VIP')->firstOrFail();

    $this->artisan('perigee:render-lounge', ['--table' => $table->id])->assertSuccessful();

    expect(Storage::disk('public')->allFiles('renders'))->toBe(["renders/tables/{$table->id}.webp"])
        ->and($renderer->shots)->toHaveCount(1);
});

test('the render commands refuse to run where the studio is off', function () {
    config(['site.render_studio' => false]);
    $renderer = fakeRenderer();

    $this->artisan('perigee:render-lounge')->assertFailed();
    $this->artisan('perigee:render-menu')->assertFailed();

    expect($renderer->shots)->toBe([]);
});

// --- Menu photos --------------------------------------------------------------------------

test('render-menu saves a generated photo the same way as an upload', function () {
    fakeRenderer();
    $item = menuItemWithoutPhoto();

    $this->artisan('perigee:render-menu')->expectsOutputToContain('Rendered 1 menu photos, skipped 0.')->assertSuccessful();

    $item->refresh();
    expect($item->photo_generated)->toBeTrue()
        ->and($item->image_path)->toStartWith('menu/')->toEndWith('.webp')
        ->and($item->thumb_path)->toBe('menu/thumbs/'.basename($item->image_path));

    Storage::disk('public')->assertExists([$item->image_path, $item->thumb_path]);
});

test('render-menu never replaces a photo that staff uploaded, even with --force', function () {
    $renderer = fakeRenderer();
    $item = menuItemWithoutPhoto();
    $staffPhoto = app(MenuPhotoStore::class)->store(UploadedFile::fake()->image('tea.jpg', 800, 600));
    $item->update($staffPhoto);

    $this->artisan('perigee:render-menu', ['--force' => true])
        ->expectsOutputToContain('Rendered 0 menu photos, skipped 1.')
        ->assertSuccessful();

    expect($item->refresh()->image_path)->toBe($staffPhoto['image_path'])
        ->and($item->photo_generated)->toBeFalse()
        ->and($renderer->shots)->toBe([]);
    Storage::disk('public')->assertExists($staffPhoto['image_path']);
});

test('render-menu renders a generated photo again only with --force', function () {
    fakeRenderer();
    $item = menuItemWithoutPhoto();

    $this->artisan('perigee:render-menu')->assertSuccessful();
    $first = $item->refresh()->only(['image_path', 'thumb_path']);

    $this->artisan('perigee:render-menu')->expectsOutputToContain('Rendered 0 menu photos, skipped 1.');
    expect($item->refresh()->image_path)->toBe($first['image_path']);

    $this->artisan('perigee:render-menu', ['--force' => true, '--item' => $item->id])->assertSuccessful();

    expect($item->refresh()->image_path)->not->toBe($first['image_path'])
        ->and($item->photo_generated)->toBeTrue();
    Storage::disk('public')->assertMissing([$first['image_path'], $first['thumb_path']]);
});

test('a staff upload over a generated photo marks it as their own', function () {
    fakeRenderer();
    $item = menuItemWithoutPhoto();
    $this->artisan('perigee:render-menu')->assertSuccessful();

    $this->actingAs(studioUser('staff'))
        ->put(route('staff.menu-items.update', ['menu_item' => $item]), [
            'menu_category_id' => $item->menu_category_id,
            'name' => ['en' => 'Mint Tea', 'lv' => 'Mint Tea', 'ru' => 'Mint Tea'],
            'price' => 6,
            'image' => UploadedFile::fake()->image('real.jpg', 900, 700),
        ])
        ->assertSessionHasNoErrors();

    expect($item->refresh()->photo_generated)->toBeFalse()
        ->and($item->image_path)->toEndWith('.jpg');
});

test('seeding the menu again keeps the photos', function () {
    $this->seed(MenuSeeder::class);
    $item = MenuItem::firstOrFail();
    $item->update(['image_path' => 'menu/kept.webp', 'thumb_path' => 'menu/thumbs/kept.webp', 'photo_generated' => true]);

    $this->seed(MenuSeeder::class);

    expect($item->refresh()->image_path)->toBe('menu/kept.webp');
});

// --- Studio page --------------------------------------------------------------------------

test('the studio page answers only to an admin or a signed link, and only where it is on', function () {
    $this->seed(TableSeeder::class);
    $table = Table::firstOrFail();
    $query = ['scene' => 'table', 'id' => $table->id];

    $this->get(route('render-studio', $query))->assertForbidden();
    $this->actingAs(studioUser('staff'))->get(route('render-studio', $query))->assertForbidden();

    $signed = app(RenderStudio::class)->url('http://localhost', 'table', $table->id);
    auth()->logout();
    $this->get($signed)->assertOk()->assertSee('studioData', false)->assertSee('"scene":"table"', false);

    $this->actingAs(studioUser('admin'))->get(route('render-studio', $query))->assertOk();
    $this->actingAs(studioUser('admin'))->get(route('render-studio', ['scene' => 'pool', 'id' => 1]))->assertSessionHasErrors('scene');

    config(['site.render_studio' => false]);
    $this->actingAs(studioUser('admin'))->get(route('render-studio', $query))->assertNotFound();
    $this->get($signed)->assertNotFound();
});

test('the studio page describes a menu item in English', function () {
    $item = menuItemWithoutPhoto('Jasmine Green Tea');

    $this->actingAs(studioUser('admin'))
        ->get(route('render-studio', ['scene' => 'menu', 'id' => $item->id]))
        ->assertOk()
        ->assertSee('"name":"Jasmine Green Tea"', false)
        ->assertSee('"category":'.json_encode('Tea & coffee', JSON_HEX_AMP), false);
});

// --- Regenerate button --------------------------------------------------------------------

test('only an admin can queue new lounge photos', function () {
    Queue::fake();

    $this->actingAs(studioUser('staff'))->post(route('admin.floors.photos.store'))->assertForbidden();
    Queue::assertNothingPushed();

    $this->actingAs(studioUser('admin'))->from(route('admin.floor-plan'))
        ->withHeader('Host', 'internal.example')
        ->post(route('admin.floors.photos.store'))
        ->assertRedirect()
        ->assertSessionHas('status');
    Queue::assertPushed(RenderLoungePhotosJob::class, fn (RenderLoungePhotosJob $job) => $job->rootUrl === config('app.url'));

    config(['site.render_studio' => false]);
    $this->actingAs(studioUser('admin'))->post(route('admin.floors.photos.store'))->assertNotFound();
});

test('deleting a table or a room also deletes its picture', function () {
    $this->seed(TableSeeder::class);
    $renders = app(LoungeRenders::class);
    $table = Table::where('name', 'T1')->firstOrFail();
    $room = Room::where('name->en', 'Bar')->firstOrFail();
    Storage::disk('public')->put($renders->tablePath($table->id), 'table');
    Storage::disk('public')->put($renders->roomPath($room->id), 'room');
    $admin = studioUser('admin');

    $this->actingAs($admin)->delete(route('admin.tables.destroy', ['table' => $table]))->assertRedirect();
    // The bar has no tables, so saving its floor without it removes the room.
    $plan = ['rooms' => Room::where('floor_id', $room->floor_id)->whereKeyNot($room->id)->get()->map(fn (Room $other) => [
        'id' => $other->id, 'name' => $other->name, 'kind' => $other->kind->value, 'color' => $other->color->value,
        'x' => $other->x, 'y' => $other->y, 'width' => $other->width, 'height' => $other->height,
    ])->all(), 'tables' => Table::whereIn('room_id', Room::where('floor_id', $room->floor_id)->pluck('id'))->get()->map(fn (Table $other) => [
        'id' => $other->id, 'name' => $other->name, 'capacity' => $other->capacity, 'x' => $other->pos_x, 'y' => $other->pos_y,
        'width' => $other->width, 'height' => $other->height, 'rotation' => $other->rotation, 'is_active' => $other->is_active,
    ])->all()];
    $this->actingAs($admin)->put(route('admin.floors.plan.update', ['floor' => $room->floor_id]), ['plan_payload' => json_encode($plan)])
        ->assertSessionHasNoErrors();

    Storage::disk('public')->assertMissing([$renders->tablePath($table->id), $renders->roomPath($room->id)]);
});

// --- Table panel and default set ----------------------------------------------------------

test('the reservations page gives each table its render, or its room render', function () {
    $this->seed(TableSeeder::class);
    $own = Table::where('name', 'T1')->firstOrFail();
    $roomOnly = Table::where('name', 'VIP')->firstOrFail();
    $none = Table::where('name', 'S1')->firstOrFail();
    $renders = app(LoungeRenders::class);

    Storage::disk('public')->put($renders->tablePath($own->id), 'table');
    Storage::disk('public')->put($renders->roomPath($own->room_id), 'room');
    Storage::disk('public')->put($renders->roomPath($roomOnly->room_id), 'room');

    $response = $this->actingAs(studioUser('client'))->get(route('client.reservations.index'))->assertOk();
    $urls = $response->viewData('tableRenders');

    expect($urls[$own->id])->toContain("renders/tables/{$own->id}.webp?v=")
        ->and($urls[$roomOnly->id])->toContain("renders/rooms/{$roomOnly->room_id}.webp?v=")
        ->and($urls[$none->id])->toBeNull();

    $response->assertSee('3D visualization')->assertSee('data-render="'.e($urls[$own->id]).'"', false);
});

test('the default set fills records without a picture and replaces nothing', function () {
    $this->seed(TableSeeder::class);
    fakeRenderer();
    $item = menuItemWithoutPhoto('Black Tea Service');
    $staffItem = menuItemWithoutPhoto('Espresso');

    $folder = storage_path('framework/testing/default-renders-'.uniqid());
    $defaults = new class(app(LoungeRenders::class), app(MenuPhotoStore::class), $folder) extends DefaultRenders
    {
        public function __construct(LoungeRenders $renders, MenuPhotoStore $photos, private string $root)
        {
            parent::__construct($renders, $photos);
        }

        public function folder(string $kind = ''): string
        {
            return $this->root.($kind === '' ? '' : '/'.$kind);
        }
    };
    app()->instance(DefaultRenders::class, $defaults);

    // Render everything and export it as the default set, as a developer would.
    $this->artisan('perigee:render-lounge', ['--export' => true])->expectsOutputToContain('Exported 65 pictures')->assertSuccessful();
    $this->artisan('perigee:render-menu', ['--export' => true])->expectsOutputToContain('Exported 2 photos')->assertSuccessful();

    expect(File::exists($folder.'/tables/vip.webp'))->toBeTrue()
        ->and(File::exists($folder.'/rooms/1-main-hall.webp'))->toBeTrue()
        ->and(File::exists($folder.'/rooms/2-cinema-room.webp'))->toBeTrue()
        ->and(File::exists($folder.'/menu/tea-coffee-black-tea-service.webp'))->toBeTrue();

    // A fresh install: no pictures, one item with a staff photo.
    Storage::fake('public');
    $staffPhoto = app(MenuPhotoStore::class)->store(UploadedFile::fake()->image('espresso.jpg', 800, 600));
    $staffItem->update([...$staffPhoto, 'photo_generated' => false]);
    $item->update(['image_path' => null, 'thumb_path' => null, 'photo_generated' => false]);
    $kept = app(LoungeRenders::class)->tablePath(Table::where('name', 'T1')->value('id'));
    Storage::disk('public')->put($kept, 'already here');

    expect($defaults->install())->toBe(65);

    expect(Storage::disk('public')->get($kept))->toBe('already here')
        ->and(Storage::disk('public')->files('renders/rooms'))->toHaveCount(25)
        ->and(Storage::disk('public')->files('renders/tables'))->toHaveCount(40)
        ->and($item->refresh()->photo_generated)->toBeTrue()
        ->and($item->image_path)->toStartWith('menu/')
        ->and($staffItem->refresh()->image_path)->toBe($staffPhoto['image_path'])
        ->and($defaults->install())->toBe(0);

    File::deleteDirectory($folder);
});
