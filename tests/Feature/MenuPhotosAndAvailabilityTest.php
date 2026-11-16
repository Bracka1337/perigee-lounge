<?php

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Table;
use App\Models\User;
use Database\Seeders\DefaultTranslationsSeeder;
use Database\Seeders\MenuSeeder;
use Database\Seeders\TableSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed([DefaultTranslationsSeeder::class, MenuSeeder::class]);
    Storage::fake('public');

    $this->staff = User::factory()->create();
    $this->staff->forceFill(['role' => 'staff', 'email_verified_at' => now()])->save();
    $this->item = MenuItem::with('category')->first();
});

function itemForm(MenuItem $item, array $extra = []): array
{
    return [
        'menu_category_id' => $item->menu_category_id,
        'name' => $item->name,
        'price' => $item->price,
        'is_available' => 1,
        ...$extra,
    ];
}

function updateItem(MenuItem $item, array $extra = [])
{
    return test()->actingAs(test()->staff)
        ->put(route('staff.menu-items.update', ['locale' => 'en', 'menu_item' => $item]), itemForm($item, $extra));
}

test('an uploaded photo is stored at most 1200 px wide with a 400 px thumbnail', function (string $file) {
    updateItem($this->item, ['image' => UploadedFile::fake()->image($file, 2000, 1500)])
        ->assertSessionHasNoErrors();

    $item = $this->item->fresh();
    $disk = Storage::disk('public');

    expect($item->image_path)->not->toContain(pathinfo($file, PATHINFO_FILENAME))
        ->and($disk->exists($item->image_path))->toBeTrue()
        ->and($disk->exists($item->thumb_path))->toBeTrue()
        ->and(getimagesizefromstring($disk->get($item->image_path))[0])->toBe(1200)
        ->and(getimagesizefromstring($disk->get($item->thumb_path))[0])->toBe(400);
})->with(['dish.jpg', 'dish.png', 'dish.webp']);

test('small photos are not enlarged', function () {
    updateItem($this->item, ['image' => UploadedFile::fake()->image('dish.jpg', 300, 200)]);

    $item = $this->item->fresh();

    expect(getimagesizefromstring(Storage::disk('public')->get($item->image_path))[0])->toBe(300);
});

test('photos must be jpg, png or webp up to 4 MB', function (UploadedFile $file) {
    updateItem($this->item, ['image' => $file])->assertSessionHasErrors('image');

    expect($this->item->fresh()->image_path)->toBeNull()
        ->and(Storage::disk('public')->allFiles())->toBe([]);
})->with([
    'gif' => fn () => UploadedFile::fake()->image('dish.gif', 100, 100),
    'pdf' => fn () => UploadedFile::fake()->create('menu.pdf', 100, 'application/pdf'),
    'over 4 MB' => fn () => UploadedFile::fake()->image('dish.jpg', 100, 100)->size(4097),
]);

test('replacing a photo removes the old files', function () {
    updateItem($this->item, ['image' => UploadedFile::fake()->image('a.jpg', 800, 600)]);
    $old = $this->item->fresh();

    updateItem($old, ['image' => UploadedFile::fake()->image('b.jpg', 800, 600)]);
    $new = $old->fresh();

    Storage::disk('public')->assertMissing([$old->image_path, $old->thumb_path]);
    Storage::disk('public')->assertExists([$new->image_path, $new->thumb_path]);
});

test('removing or deleting a photo removes its files', function () {
    updateItem($this->item, ['image' => UploadedFile::fake()->image('a.jpg', 800, 600)]);
    $withPhoto = $this->item->fresh();

    updateItem($withPhoto, ['remove_image' => 1]);

    Storage::disk('public')->assertMissing([$withPhoto->image_path, $withPhoto->thumb_path]);
    expect($withPhoto->fresh()->image_path)->toBeNull();

    updateItem($withPhoto, ['image' => UploadedFile::fake()->image('b.jpg', 800, 600)]);
    $again = $withPhoto->fresh();

    $this->actingAs($this->staff)->delete(route('staff.menu-items.destroy', ['locale' => 'en', 'menu_item' => $again]));

    Storage::disk('public')->assertMissing([$again->image_path, $again->thumb_path]);
});

test('menus use the thumbnail and show a placeholder for items without a photo', function () {
    updateItem($this->item, ['image' => UploadedFile::fake()->image('a.jpg', 800, 600)]);
    $item = $this->item->fresh();

    $this->get('/en/menu')
        ->assertOk()
        ->assertSee('src="'.Storage::disk('public')->url($item->thumb_path).'"', false)
        ->assertSee('loading="lazy"', false)
        ->assertSee('data-menu-photo-placeholder', false);
});

test('unavailable items stay on the qr menu, labelled and without an add button', function () {
    $this->seed(TableSeeder::class);
    MenuItem::query()->update(['is_available' => false]);

    $this->actingAs(User::factory()->create())
        ->get('/en/menu/table-z4')
        ->assertOk()
        ->assertSee($this->item->t('name'))
        ->assertSee('data-unavailable', false)
        ->assertSee('Unavailable')
        ->assertDontSee('name="menu_item_id"', false);
});

test('a forged add-to-cart post for an unavailable item is refused', function () {
    $this->seed(TableSeeder::class);
    $this->item->update(['is_available' => false]);

    $this->actingAs(User::factory()->create())
        ->from('/en/menu/table-z4')
        ->post(route('client.table.cart.add', ['locale' => 'en', 'code' => 'z4']), ['menu_item_id' => $this->item->id])
        ->assertSessionHas('error');

    expect(session()->get('client_table_cart_'.Table::where('name', 'Z4')->value('id'), []))->toBe([]);
});

test('the available-only menu leaves out unavailable items', function () {
    $this->item->update(['is_available' => false]);

    $this->get('/en/menu')->assertSee($this->item->t('name'));

    $this->get('/en/menu?available=1')
        ->assertOk()
        ->assertDontSee($this->item->t('name'))
        ->assertSee('aria-pressed="true"', false)
        ->assertSee('id="category-'.$this->item->menu_category_id.'"', false);
});

test('staff can switch an item and a category between available and unavailable', function () {
    $category = $this->item->category;

    $this->actingAs($this->staff)
        ->patch(route('staff.menu-items.availability', ['locale' => 'en', 'menuItem' => $this->item]), ['is_available' => 0])
        ->assertRedirect(route('staff.menu-categories.index', ['locale' => 'en']).'#staff-category-'.$category->id);
    expect($this->item->fresh()->is_available)->toBeFalse();

    $this->patch(route('staff.menu-items.availability', ['locale' => 'en', 'menuItem' => $this->item]), ['is_available' => 1]);
    expect($this->item->fresh()->is_available)->toBeTrue();

    $this->patch(route('staff.menu-categories.availability', ['locale' => 'en', 'menuCategory' => $category]), ['is_available' => 0]);
    expect($category->fresh()->is_available)->toBeFalse()
        ->and($this->item->fresh()->is_available)->toBeTrue();
});

test('the staff menu list has a toggle per item and per category', function () {
    $this->actingAs($this->staff)
        ->get(route('staff.menu-categories.index', ['locale' => 'en']))
        ->assertOk()
        ->assertSee(route('staff.menu-items.availability', ['locale' => 'en', 'menuItem' => $this->item]), false)
        ->assertSee(route('staff.menu-categories.availability', ['locale' => 'en', 'menuCategory' => $this->item->menu_category_id]), false);
});

test('clients cannot change availability', function () {
    $this->actingAs(User::factory()->create())
        ->patch(route('staff.menu-items.availability', ['locale' => 'en', 'menuItem' => $this->item]), ['is_available' => 0])
        ->assertForbidden();

    expect($this->item->fresh()->is_available)->toBeTrue()
        ->and(MenuCategory::where('is_available', false)->exists())->toBeFalse();
});
