<?php

use App\Models\Floor;
use App\Models\Room;
use App\Models\Table;
use App\Models\User;
use App\Services\LoungeLayout;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\DefaultTranslationsSeeder;
use Database\Seeders\TableSeeder;
use Illuminate\Support\Facades\DB;

function loungeUser(string $role): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => $role, 'email_verified_at' => now()])->save();

    return $user;
}

function loungeFloor(string $name = 'Ground floor', int $order = 0): Floor
{
    return Floor::create(['name' => ['en' => $name, 'lv' => $name, 'ru' => $name], 'sort_order' => $order]);
}

function loungeRoom(Floor $floor, array $attributes = []): Room
{
    return Room::create([
        'floor_id' => $floor->id,
        'name' => ['en' => 'Hall', 'lv' => 'Zāle', 'ru' => 'Зал'],
        'kind' => 'seating',
        'color' => 'violet',
        'x' => 0, 'y' => 0, 'width' => 10, 'height' => 8,
        ...$attributes,
    ]);
}

/**
 * The floor as the plan editor would post it, with optional changes.
 *
 * @return array{rooms: list<array<string, mixed>>, tables: list<array<string, mixed>>}
 */
function planOf(Floor $floor): array
{
    $floor->load('rooms.tables');

    return [
        'rooms' => $floor->rooms->map(fn (Room $room) => [
            'id' => $room->id, 'name' => $room->name, 'kind' => $room->kind->value, 'color' => $room->color->value,
            'is_bookable_whole' => $room->is_bookable_whole, 'x' => $room->x, 'y' => $room->y, 'width' => $room->width, 'height' => $room->height,
        ])->values()->all(),
        'tables' => $floor->rooms->flatMap(fn (Room $room) => $room->tables)->map(fn (Table $table) => [
            'id' => $table->id, 'name' => $table->name, 'capacity' => $table->capacity, 'x' => $table->pos_x, 'y' => $table->pos_y,
            'width' => $table->width, 'height' => $table->height, 'rotation' => $table->rotation, 'is_active' => $table->is_active,
        ])->values()->all(),
    ];
}

function savePlan(User $admin, Floor $floor, array $plan)
{
    return test()->actingAs($admin)->put(route('admin.floors.plan.update', ['floor' => $floor]), ['plan_payload' => json_encode($plan)]);
}

function runLayoutMigrations(): void
{
    (require database_path('migrations/2026_11_16_210300_apply_default_lounge_layout.php'))->up();
    (require database_path('migrations/2026_11_17_100000_store_lounge_layout_in_metres.php'))->up();
}

beforeEach(function () {
    $this->seed(DefaultTranslationsSeeder::class);
});

// --- Default layout: data migrations and seeders ------------------------------------------

test('an old database ends up in metres with every table kept and inside a room', function () {
    // The layout before floors existed, minus P2, which an admin removed at some point.
    foreach (['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'B1', 'B2', 'B3', 'B4', 'B5', 'B6', 'B7',
        'Z1', 'Z2', 'Z3', 'Z4', 'Z5', 'Z6', 'Z7', 'Z8', 'P1', 'VIP'] as $name) {
        DB::table('tables')->insert(['name' => $name, 'capacity' => 5, 'pos_x' => 1, 'pos_y' => 1, 'width' => 50, 'height' => 50]);
    }
    DB::table('tables')->insert(['name' => 'Custom', 'capacity' => 3, 'pos_x' => 50, 'pos_y' => 40, 'width' => 80, 'height' => 120]);
    $idsBefore = Table::pluck('id', 'name')->all();

    runLayoutMigrations();

    expect(Floor::count())->toBe(2)
        ->and(Room::count())->toBe(26)
        ->and(Room::where('kind', 'corridor')->count())->toBe(6)
        ->and(Table::count())->toBe(40)
        ->and(Table::where('name', 'P2')->exists())->toBeFalse()
        ->and(Table::whereIn('name', array_keys($idsBefore))->pluck('id', 'name')->all())->toBe($idsBefore);

    $vip = Table::with('room')->where('name', 'VIP')->firstOrFail();
    expect($vip->room->t('name'))->toBe('VIP')
        ->and($vip->capacity)->toBe(5)
        ->and($vip->room->contains($vip->pos_x, $vip->pos_y))->toBeTrue()
        ->and($vip->width)->toBe(525);

    // A table the layout does not know keeps its place, converted to metres and centimetres.
    $custom = Table::where('name', 'Custom')->firstOrFail();
    expect($custom->pos_x)->toBe(15.0)
        ->and($custom->pos_y)->toBe(10.5)
        ->and([$custom->width, $custom->height])->toBe([200, 300]);
});

test('the data migrations leave a database without tables empty', function () {
    runLayoutMigrations();

    expect(Floor::count())->toBe(0)->and(Table::count())->toBe(0);
});

test('seeding gives two floors without gaps or overlaps and every table inside its room', function () {
    $this->seed(TableSeeder::class);

    expect(Floor::count())->toBe(2)->and(Room::count())->toBe(26)->and(Table::count())->toBe(40);

    foreach (Floor::with('rooms')->get() as $floor) {
        $rooms = $floor->rooms->values();
        $area = $rooms->sum(fn (Room $room) => $room->width * $room->height);
        $minX = $rooms->min('x');
        $minY = $rooms->min('y');
        $maxX = $rooms->max(fn (Room $room) => $room->x + $room->width);
        $maxY = $rooms->max(fn (Room $room) => $room->y + $room->height);

        // The rooms cover the building exactly once: no gaps and no overlaps.
        expect($area)->toBe(($maxX - $minX) * ($maxY - $minY), "{$floor->t('name')} has gaps or overlaps");
    }

    foreach (Table::with('room')->get() as $table) {
        expect($table->room->contains($table->pos_x, $table->pos_y))->toBeTrue("{$table->name} is outside its room");
    }
});

test('the seeder adds a demo client that can sign in', function () {
    $this->seed(AdminUserSeeder::class);

    $client = User::where('email', 'client@perigee.test')->firstOrFail();
    expect($client->isClient())->toBeTrue()->and($client->hasVerifiedEmail())->toBeTrue();
});

// --- Access -------------------------------------------------------------------------------

test('only admins can open or change the floor plan', function (string $role, int $status) {
    $floor = loungeFloor();
    loungeRoom($floor);
    $user = loungeUser($role);

    $this->actingAs($user)->get(route('admin.floor-plan'))->assertStatus($status);
    savePlan($user, $floor, planOf($floor))->assertStatus($status === 200 ? 302 : $status);
    $this->actingAs($user)->post(route('admin.floors.store'), ['name' => ['en' => 'Roof']])->assertStatus($status === 200 ? 302 : $status);

    expect(Floor::where('name->en', 'Roof')->exists())->toBe($role === 'admin');
})->with([
    ['admin', 200],
    ['staff', 403],
    ['client', 403],
]);

test('guests are sent to login, and the old pages lead to the floor plan', function () {
    $this->get(route('admin.floor-plan'))->assertRedirectContains('/login');

    $admin = loungeUser('admin');
    $this->actingAs($admin)->get('/en/admin/tables')->assertRedirect(route('admin.floor-plan'));
    $this->actingAs($admin)->get('/en/admin/floors')->assertRedirect(route('admin.floor-plan'));
});

// --- Floors -------------------------------------------------------------------------------

test('an admin can add, rename and reorder floors', function () {
    $admin = loungeUser('admin');
    $ground = loungeFloor('Ground floor', 0);

    $this->actingAs($admin)->post(route('admin.floors.store'), ['name' => ['en' => 'Roof', 'lv' => 'Jumts', 'ru' => '']])
        ->assertSessionHasNoErrors();
    $roof = Floor::where('name->en', 'Roof')->firstOrFail();
    expect($roof->name)->toBe(['en' => 'Roof', 'lv' => 'Jumts', 'ru' => 'Roof']);

    $this->actingAs($admin)->put(route('admin.floors.update', ['floor' => $roof]), ['name' => ['en' => 'Rooftop']])->assertSessionHasNoErrors();
    expect($roof->refresh()->t('name'))->toBe('Rooftop');

    $this->actingAs($admin)->post(route('admin.floors.move', ['floor' => $roof]), ['direction' => 'up'])
        ->assertRedirect(route('admin.floor-plan', ['floor' => $roof->id]));
    expect(Floor::orderBy('sort_order')->pluck('id')->all())->toBe([$roof->id, $ground->id]);
});

test('a floor with rooms cannot be deleted, an empty one can', function () {
    $admin = loungeUser('admin');
    $floor = loungeFloor();
    loungeRoom($floor);

    $this->actingAs($admin)->from(route('admin.floor-plan'))
        ->delete(route('admin.floors.destroy', ['floor' => $floor]))
        ->assertSessionHas('error', 'This floor still has rooms. Delete or move them first.');

    savePlan($admin, $floor, ['rooms' => [], 'tables' => []])->assertSessionHasNoErrors();
    $this->actingAs($admin)->delete(route('admin.floors.destroy', ['floor' => $floor]))->assertSessionMissing('error');

    expect(Floor::count())->toBe(0);
});

// --- Saving the plan ----------------------------------------------------------------------

test('saving the plan adds, changes and removes rooms and places tables in them', function () {
    $admin = loungeUser('admin');
    $floor = loungeFloor();
    $hall = loungeRoom($floor);
    $old = loungeRoom($floor, ['name' => ['en' => 'Old', 'lv' => 'Old', 'ru' => 'Old'], 'x' => 10, 'width' => 4]);
    $table = Table::create(['name' => 'A1', 'capacity' => 4, 'room_id' => $hall->id, 'pos_x' => 2, 'pos_y' => 2, 'width' => 200, 'height' => 200]);

    $plan = planOf($floor);
    $plan['rooms'] = [
        [...$plan['rooms'][0], 'name' => ['en' => 'Big hall', 'lv' => '', 'ru' => ''], 'color' => 'amber', 'is_bookable_whole' => true],
        ['id' => null, 'name' => ['en' => 'Bar', 'lv' => 'Bārs', 'ru' => 'Бар'], 'kind' => 'bar', 'color' => 'teal', 'x' => 10, 'y' => 0, 'width' => 6, 'height' => 8],
    ];
    $plan['tables'][0] = [...$plan['tables'][0], 'x' => 13, 'y' => 4, 'rotation' => 90, 'capacity' => 2];
    $plan['tables'][] = ['id' => null, 'name' => 'A2', 'capacity' => 6, 'x' => 5, 'y' => 5, 'width' => 300, 'height' => 200, 'rotation' => 0, 'is_active' => false];

    savePlan($admin, $floor, $plan)
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.floor-plan', ['floor' => $floor->id]));

    $hall->refresh();
    $bar = Room::where('name->en', 'Bar')->firstOrFail();
    expect(Room::find($old->id))->toBeNull()
        ->and($hall->t('name'))->toBe('Big hall')
        ->and($hall->name['lv'])->toBe('Big hall')
        ->and($hall->is_bookable_whole)->toBeTrue()
        ->and($table->refresh()->room_id)->toBe($bar->id)
        ->and($table->rotation)->toBe(90)
        ->and($table->capacity)->toBe(2)
        ->and(Table::where('name', 'A2')->firstOrFail()->only(['room_id', 'is_active', 'width']))
        ->toBe(['room_id' => $hall->id, 'is_active' => false, 'width' => 300]);
});

test('rooms may touch but not overlap', function () {
    $admin = loungeUser('admin');
    $floor = loungeFloor();
    loungeRoom($floor);

    $touching = planOf($floor);
    $touching['rooms'][] = ['id' => null, 'name' => ['en' => 'Next door'], 'kind' => 'seating', 'color' => 'lime', 'x' => 10, 'y' => 0, 'width' => 5, 'height' => 8];
    savePlan($admin, $floor, $touching)->assertSessionHasNoErrors();

    $overlapping = planOf($floor);
    $overlapping['rooms'][] = ['id' => null, 'name' => ['en' => 'Clash'], 'kind' => 'seating', 'color' => 'rose', 'x' => 14, 'y' => 7, 'width' => 3, 'height' => 3];
    savePlan($admin, $floor, $overlapping)->assertSessionHasErrors();

    expect(Room::where('name->en', 'Clash')->exists())->toBeFalse();
});

test('the plan rejects bad rooms and tables', function (string $case) {
    $admin = loungeUser('admin');
    $floor = loungeFloor();
    $room = loungeRoom($floor);
    Table::create(['name' => 'A1', 'capacity' => 4, 'room_id' => $room->id, 'pos_x' => 2, 'pos_y' => 2, 'width' => 200, 'height' => 200]);
    Table::create(['name' => 'Taken', 'capacity' => 4]);

    $plan = planOf($floor);
    match ($case) {
        'room too small' => $plan['rooms'][0]['width'] = 0.5,
        'unknown color' => $plan['rooms'][0]['color'] = 'neon',
        'blank room name' => $plan['rooms'][0]['name']['en'] = '   ',
        'table outside every room' => $plan['tables'][0]['x'] = 30,
        'table name used on another floor' => $plan['tables'][0]['name'] = 'taken',
        'two tables with one name' => $plan['tables'][] = [...$plan['tables'][0], 'id' => null],
        'tiny table' => $plan['tables'][0]['width'] = 10,
    };

    savePlan($admin, $floor, $plan)->assertSessionHasErrors();
    expect($room->refresh()->width)->toBe(10.0)->and(Table::count())->toBe(2);
})->with(['room too small', 'unknown color', 'blank room name', 'table outside every room',
    'table name used on another floor', 'two tables with one name', 'tiny table']);

test('a room of another floor cannot be changed through this floor', function () {
    $admin = loungeUser('admin');
    $floor = loungeFloor();
    $foreign = loungeRoom(loungeFloor('Second floor', 1));

    savePlan($admin, $floor, ['rooms' => [planOf($foreign->floor)['rooms'][0]], 'tables' => []])->assertSessionHasErrors();

    expect($foreign->refresh()->floor_id)->not->toBe($floor->id);
});

test('a floor cannot take over or leave out tables', function () {
    $admin = loungeUser('admin');
    $ground = loungeFloor();
    $hall = loungeRoom($ground);
    $upstairs = loungeRoom(loungeFloor('Second floor', 1));
    $own = Table::create(['name' => 'A1', 'capacity' => 4, 'room_id' => $hall->id, 'pos_x' => 2, 'pos_y' => 2]);
    $foreign = Table::create(['name' => 'K9', 'capacity' => 8, 'room_id' => $upstairs->id, 'pos_x' => 3, 'pos_y' => 3]);

    // A table of the other floor, sent with this floor's plan, is refused.
    $plan = planOf($ground);
    $plan['tables'][] = [...$plan['tables'][0], 'id' => $foreign->id, 'name' => 'K9', 'capacity' => 2, 'x' => 5, 'y' => 5];
    savePlan($admin, $ground, $plan)->assertSessionHasErrors('tables.1.id');
    expect($foreign->refresh()->room_id)->toBe($upstairs->id)->and($foreign->capacity)->toBe(8);

    // Leaving one of the floor's own tables out is refused too.
    savePlan($admin, $ground, [...planOf($ground), 'tables' => []])->assertSessionHasErrors('tables');
    expect($own->refresh()->room_id)->toBe($hall->id);
});

test('a room that still has tables cannot be removed from the plan', function () {
    $admin = loungeUser('admin');
    $floor = loungeFloor();
    $room = loungeRoom($floor);
    Table::create(['name' => 'A1', 'capacity' => 4, 'room_id' => $room->id, 'pos_x' => 2, 'pos_y' => 2]);

    // Sending the table elsewhere is not possible here, so the room has to stay.
    $this->actingAs($admin)->from(route('admin.floor-plan'))
        ->put(route('admin.floors.plan.update', ['floor' => $floor]), ['plan_payload' => json_encode(['rooms' => [], 'tables' => []])])
        ->assertSessionHasErrors('tables');

    expect(Room::find($room->id))->not->toBeNull();
});

test('a table can be moved to a room on another floor, and deleted', function () {
    $admin = loungeUser('admin');
    $hall = loungeRoom(loungeFloor());
    $cabin = loungeRoom(loungeFloor('Second floor', 1), ['x' => 20, 'y' => 10, 'width' => 4, 'height' => 6]);
    $table = Table::create(['name' => 'A1', 'capacity' => 4, 'room_id' => $hall->id, 'pos_x' => 2, 'pos_y' => 2]);

    $this->actingAs($admin)
        ->put(route('admin.tables.update', ['table' => $table]), ['name' => 'A1', 'capacity' => 4, 'room_id' => $cabin->id, 'is_active' => 1])
        ->assertRedirect(route('admin.floor-plan', ['floor' => $cabin->floor_id]));

    $table->refresh();
    expect($table->room_id)->toBe($cabin->id)->and($table->pos_x)->toBe(22.0)->and($table->pos_y)->toBe(13.0);

    $this->actingAs($admin)->delete(route('admin.tables.destroy', ['table' => $table]))->assertRedirect();
    expect(Table::find($table->id))->toBeNull();
});

// --- Pages that show the plan -------------------------------------------------------------

test('each floor lists only its own tables, drawn to the building scale', function () {
    $this->seed(TableSeeder::class);
    Table::where('name', 'T1')->update(['is_active' => false]);

    $layout = app(LoungeLayout::class);
    [$ground, $second] = $layout->floors()->all();

    expect($ground->tables->pluck('name'))->toContain('VIP')->not->toContain('S1', 'T1')
        ->and($second->tables)->toHaveCount(15);

    $frame = $layout->frame($layout->floors());
    expect($frame->aspectRatio())->toBe('31 / 27');

    $client = loungeUser('client');
    $this->actingAs($client)->get(route('client.reservations.index'))
        ->assertOk()
        ->assertViewHas('activeFloorId', $ground->id)
        ->assertSee('aspect-ratio: 31 / 27', false)
        ->assertSee('data-floor-tab="'.$second->id.'"', false);

    $this->actingAs($client)->get(route('client.reservations.index', ['floor' => $second->id]))->assertViewHas('activeFloorId', $second->id);
    $this->actingAs(loungeUser('staff'))->get(route('staff.orders.create', ['floor' => 'nope']))->assertOk()->assertViewHas('activeFloorId', $ground->id);
});

test('pages with the plan still work before any floor exists', function () {
    Table::create(['name' => 'A1', 'capacity' => 4]);

    $this->actingAs(loungeUser('client'))->get(route('client.reservations.index'))->assertOk()->assertSee('A1');
    $this->actingAs(loungeUser('staff'))->get(route('staff.reservations.index'))->assertOk()->assertSee('A1');
    $this->actingAs(loungeUser('admin'))->get(route('admin.floor-plan'))->assertOk();
});

test('the 3D view gets the same floors, rooms and tables as the plan, in metres', function () {
    $this->seed(TableSeeder::class);
    Table::where('name', 'T1')->update(['is_active' => false]);
    Room::where('name->en', 'VIP')->update(['is_bookable_whole' => true]);
    app()->setLocale('lv');

    $layout = app(LoungeLayout::class);
    $data = $layout->toArray($layout->floors());
    [$ground, $second] = $data['floors'];
    $vipRoom = collect($ground['rooms'])->firstWhere('name', 'VIP');
    $vipTable = collect($ground['tables'])->firstWhere('name', 'VIP');

    expect($data['frame'])->toBe(['x' => -0.5, 'y' => -0.5, 'width' => 31.0, 'depth' => 27.0])
        ->and($ground['name'])->toBe('Pirmais stāvs')
        ->and($ground['rooms'])->toHaveCount(12)
        ->and(collect($ground['tables'])->pluck('name'))->toHaveCount(24)->not->toContain('T1')
        ->and($vipRoom)->toMatchArray(['kind' => 'seating', 'color' => '#F59A7C', 'x' => 21.0, 'width' => 9.0, 'bookableWhole' => true])
        ->and($vipTable)->toMatchArray(['roomId' => $vipRoom['id'], 'capacity' => 20, 'width' => 525])
        ->and(collect($second['tables'])->pluck('name'))->toContain('K1', 'S4');
});
