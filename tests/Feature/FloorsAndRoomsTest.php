<?php

use App\Models\Floor;
use App\Models\Room;
use App\Models\Table;
use App\Models\User;
use App\Services\LoungeLayout;
use Database\Seeders\DefaultTranslationsSeeder;
use Database\Seeders\TableSeeder;
use Illuminate\Support\Facades\DB;

function loungeUser(string $role): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => $role, 'email_verified_at' => now()])->save();

    return $user;
}

function loungeRoom(Floor $floor, array $attributes = []): Room
{
    return Room::create([
        'floor_id' => $floor->id,
        'name' => ['en' => 'Hall', 'lv' => 'Zāle', 'ru' => 'Зал'],
        'kind' => 'seating',
        'color' => 'violet',
        'x' => 10, 'y' => 10, 'width' => 40, 'height' => 40,
        ...$attributes,
    ]);
}

function loungeFloor(string $name = 'Ground floor', int $order = 0): Floor
{
    return Floor::create(['name' => ['en' => $name, 'lv' => $name, 'ru' => $name], 'sort_order' => $order]);
}

function roomPayload(array $overrides = []): array
{
    return [
        'name' => ['en' => 'Cellar', 'lv' => '', 'ru' => ''],
        'kind' => 'seating',
        'color' => 'teal',
        'x' => 10, 'y' => 10, 'width' => 30, 'height' => 30,
        ...$overrides,
    ];
}

function runLayoutMigration(): void
{
    (require database_path('migrations/2026_11_16_210300_apply_default_lounge_layout.php'))->up();
}

beforeEach(function () {
    $this->seed(DefaultTranslationsSeeder::class);
});

// --- Default layout: data migration and seeders -------------------------------------------

test('the data migration moves existing tables into rooms and keeps their ids', function () {
    // The old layout without P2, which an admin removed at some point.
    $oldNames = ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'B1', 'B2', 'B3', 'B4', 'B5', 'B6', 'B7',
        'Z1', 'Z2', 'Z3', 'Z4', 'Z5', 'Z6', 'Z7', 'Z8', 'P1', 'VIP'];

    foreach ($oldNames as $name) {
        DB::table('tables')->insert(['name' => $name, 'capacity' => 5, 'pos_x' => 1, 'pos_y' => 1, 'width' => 50, 'height' => 50]);
    }
    DB::table('tables')->insert(['name' => 'Custom', 'capacity' => 3, 'pos_x' => 44, 'pos_y' => 44, 'width' => 50, 'height' => 50]);

    $idsBefore = Table::pluck('id', 'name')->all();

    runLayoutMigration();
    runLayoutMigration();

    expect(Floor::count())->toBe(2)
        ->and(Room::count())->toBe(20)
        ->and(Table::count())->toBe(40)
        ->and(Table::where('name', 'P2')->exists())->toBeFalse()
        ->and(Table::whereIn('name', array_keys($idsBefore))->pluck('id', 'name')->all())->toBe($idsBefore);

    $vip = Table::with('room.floor')->where('name', 'VIP')->firstOrFail();
    expect($vip->room->t('name'))->toBe('VIP')
        ->and($vip->room->floor->sort_order)->toBe(0)
        ->and($vip->capacity)->toBe(5)
        ->and((float) $vip->pos_x)->toBe(84.0);

    $cabin = Table::with('room.floor')->where('name', 'S1')->firstOrFail();
    expect($cabin->room->floor->sort_order)->toBe(1);

    $custom = Table::where('name', 'Custom')->firstOrFail();
    expect($custom->room_id)->toBeNull()
        ->and((float) $custom->pos_x)->toBe(44.0);
});

test('the data migration leaves a database without tables empty', function () {
    runLayoutMigration();

    expect(Floor::count())->toBe(0)
        ->and(Table::count())->toBe(0);
});

test('seeding gives two floors with every table inside its room', function () {
    $this->seed(TableSeeder::class);

    expect(Floor::count())->toBe(2)
        ->and(Room::count())->toBe(20)
        ->and(Table::count())->toBe(40);

    foreach (Room::all() as $room) {
        expect($room->x + $room->width)->toBeLessThanOrEqual(100.0, "{$room->t('name')} leaves the plan")
            ->and($room->y + $room->height)->toBeLessThanOrEqual(100.0, "{$room->t('name')} leaves the plan");
    }

    foreach (Table::with('room')->get() as $table) {
        expect($table->room)->not->toBeNull("{$table->name} has no room")
            ->and($table->room->contains((float) $table->pos_x, (float) $table->pos_y))->toBeTrue("{$table->name} is outside its room");
    }
});

// --- Access -------------------------------------------------------------------------------

test('only admins can open or change floors and rooms', function (string $role, int $status) {
    $floor = loungeFloor();
    $room = loungeRoom($floor);
    $user = loungeUser($role);

    $this->actingAs($user)->get(route('admin.floors.index'))->assertStatus($status);
    $this->actingAs($user)->post(route('admin.floors.store'), ['name' => ['en' => 'Roof']])->assertStatus($status === 200 ? 302 : $status);
    $this->actingAs($user)->put(route('admin.floors.rooms.update', ['floor' => $floor]), ['rooms_payload' => '[]'])->assertStatus($status === 200 ? 302 : $status);
    $this->actingAs($user)->delete(route('admin.rooms.destroy', ['room' => $room]))->assertStatus($status === 200 ? 302 : $status);

    expect(Floor::where('name->en', 'Roof')->exists())->toBe($role === 'admin');
})->with([
    ['admin', 200],
    ['staff', 403],
    ['client', 403],
]);

test('guests are sent to login from the floors page', function () {
    $this->get(route('admin.floors.index'))->assertRedirectContains('/login');
});

// --- Floors -------------------------------------------------------------------------------

test('an admin can add, rename and reorder floors', function () {
    $admin = loungeUser('admin');
    $ground = loungeFloor('Ground floor', 0);

    $this->actingAs($admin)->post(route('admin.floors.store'), ['name' => ['en' => 'Roof', 'lv' => 'Jumts', 'ru' => '']])
        ->assertSessionHasNoErrors();

    $roof = Floor::where('name->en', 'Roof')->firstOrFail();
    expect($roof->name)->toBe(['en' => 'Roof', 'lv' => 'Jumts', 'ru' => 'Roof']);

    $this->actingAs($admin)->put(route('admin.floors.update', ['floor' => $roof]), ['name' => ['en' => 'Rooftop']])
        ->assertSessionHasNoErrors();
    expect($roof->refresh()->t('name'))->toBe('Rooftop');

    $this->actingAs($admin)->post(route('admin.floors.move', ['floor' => $roof]), ['direction' => 'up'])->assertRedirect();
    expect(Floor::orderBy('sort_order')->pluck('id')->all())->toBe([$roof->id, $ground->id]);

    $this->actingAs($admin)->post(route('admin.floors.store'), ['name' => ['en' => '']])->assertSessionHasErrors('name.en');
});

test('a floor with rooms cannot be deleted, an empty one can', function () {
    $admin = loungeUser('admin');
    $floor = loungeFloor();
    $room = loungeRoom($floor);

    $this->actingAs($admin)->from(route('admin.floors.index'))
        ->delete(route('admin.floors.destroy', ['floor' => $floor]))
        ->assertRedirect(route('admin.floors.index'))
        ->assertSessionHas('error', 'This floor still has rooms. Delete or move them first.');
    expect(Floor::count())->toBe(1);

    $room->delete();

    $this->actingAs($admin)->delete(route('admin.floors.destroy', ['floor' => $floor]))->assertSessionMissing('error');
    expect(Floor::count())->toBe(0);
});

// --- Rooms --------------------------------------------------------------------------------

test('the plan editor adds new rooms and updates existing ones', function () {
    $admin = loungeUser('admin');
    $floor = loungeFloor();
    $room = loungeRoom($floor);

    $payload = [
        roomPayload(['id' => $room->id, 'name' => ['en' => 'Big hall', 'lv' => 'Lielā zāle', 'ru' => 'Большой зал'], 'color' => 'amber', 'x' => 0, 'y' => 0, 'width' => 60, 'height' => 100]),
        roomPayload(['x' => 62.5, 'y' => 70, 'width' => 37.5, 'height' => 30]),
    ];

    $this->actingAs($admin)
        ->put(route('admin.floors.rooms.update', ['floor' => $floor]), ['rooms_payload' => json_encode($payload)])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.floors.index', ['floor' => $floor->id]));

    $room->refresh();
    $cellar = Room::where('name->en', 'Cellar')->firstOrFail();

    expect(Room::count())->toBe(2)
        ->and($room->t('name'))->toBe('Big hall')
        ->and($room->color->value)->toBe('amber')
        ->and($room->width)->toBe(60.0)
        ->and($cellar->floor_id)->toBe($floor->id)
        ->and($cellar->name['ru'])->toBe('Cellar')
        ->and($cellar->x)->toBe(62.5);
});

test('rooms that leave the plan or have bad values are rejected', function (array $overrides) {
    $admin = loungeUser('admin');
    $floor = loungeFloor();

    $this->actingAs($admin)
        ->put(route('admin.floors.rooms.update', ['floor' => $floor]), ['rooms_payload' => json_encode([roomPayload($overrides)])])
        ->assertSessionHasErrors();

    expect(Room::count())->toBe(0);
})->with([
    'past the right edge' => [['x' => 80, 'width' => 30]],
    'past the bottom edge' => [['y' => 75, 'height' => 30]],
    'negative position' => [['x' => -5]],
    'too small' => [['width' => 1]],
    'unknown color' => [['color' => 'neon']],
    'unknown kind' => [['kind' => 'pool']],
    'no English name' => [['name' => ['en' => '']]],
    'blank English name' => [['name' => ['en' => '   ']]],
]);

test('the plan editor cannot change a room of another floor', function () {
    $admin = loungeUser('admin');
    $floor = loungeFloor();
    $foreign = loungeRoom(loungeFloor('Second floor', 1));

    $this->actingAs($admin)
        ->put(route('admin.floors.rooms.update', ['floor' => $floor]), ['rooms_payload' => json_encode([roomPayload(['id' => $foreign->id])])])
        ->assertSessionHasErrors();

    expect($foreign->refresh()->t('name'))->toBe('Hall');
});

test('a room with tables cannot be deleted, an empty one can', function () {
    $admin = loungeUser('admin');
    $room = loungeRoom(loungeFloor());
    $table = Table::create(['name' => 'A1', 'capacity' => 4, 'room_id' => $room->id]);

    $this->actingAs($admin)->from(route('admin.floors.index'))
        ->delete(route('admin.rooms.destroy', ['room' => $room]))
        ->assertSessionHas('error', 'This room still has tables. Move them to another room first.');
    expect(Room::count())->toBe(1);

    $table->delete();

    $this->actingAs($admin)->delete(route('admin.rooms.destroy', ['room' => $room]))->assertSessionMissing('error');
    expect(Room::count())->toBe(0);
});

// --- Tables and floors --------------------------------------------------------------------

test('the table form moves a table to another room and into its bounds', function () {
    $admin = loungeUser('admin');
    $hall = loungeRoom(loungeFloor());
    $cabin = loungeRoom(loungeFloor('Second floor', 1), ['x' => 60, 'y' => 60, 'width' => 20, 'height' => 30]);
    $table = Table::create(['name' => 'A1', 'capacity' => 4, 'room_id' => $hall->id, 'pos_x' => 20, 'pos_y' => 20]);

    $this->actingAs($admin)
        ->put(route('admin.tables.update', ['table' => $table]), ['name' => 'A1', 'capacity' => 4, 'room_id' => $cabin->id, 'is_active' => 1])
        ->assertSessionHasNoErrors();

    $table->refresh();
    expect($table->room_id)->toBe($cabin->id)
        ->and((float) $table->pos_x)->toBe(70.0)
        ->and((float) $table->pos_y)->toBe(75.0);

    $this->actingAs($admin)
        ->put(route('admin.tables.update', ['table' => $table]), ['name' => 'A1', 'capacity' => 4, 'room_id' => 999999])
        ->assertSessionHasErrors('room_id');
});

test('saving the table layout puts a table in the room it was dropped in', function () {
    $admin = loungeUser('admin');
    $floor = loungeFloor();
    $hall = loungeRoom($floor);
    $bar = loungeRoom($floor, ['name' => ['en' => 'Bar', 'lv' => 'Bārs', 'ru' => 'Бар'], 'x' => 60, 'y' => 10, 'width' => 30, 'height' => 30]);
    $moved = Table::create(['name' => 'A1', 'capacity' => 4, 'room_id' => $hall->id, 'pos_x' => 20, 'pos_y' => 20]);
    $inWalkway = Table::create(['name' => 'A2', 'capacity' => 4, 'room_id' => $hall->id, 'pos_x' => 30, 'pos_y' => 30]);

    $layout = [
        ['id' => $moved->id, 'pos_x' => 75, 'pos_y' => 25, 'width' => 100, 'height' => 80, 'rotation' => 0],
        ['id' => $inWalkway->id, 'pos_x' => 55, 'pos_y' => 80, 'width' => 100, 'height' => 80, 'rotation' => 0],
    ];

    $this->actingAs($admin)
        ->post(route('admin.tables.layout.update'), ['layout_payload' => json_encode($layout)])
        ->assertSessionHasNoErrors();

    expect($moved->refresh()->room_id)->toBe($bar->id)
        ->and($inWalkway->refresh()->room_id)->toBe($hall->id)
        ->and((float) $inWalkway->pos_x)->toBe(55.0);
});

test('each floor lists only its own tables and the requested floor opens first', function () {
    $this->seed(TableSeeder::class);
    Table::create(['name' => 'Loose', 'capacity' => 2]);
    Table::where('name', 'T1')->update(['is_active' => false]);

    [$ground, $second] = app(LoungeLayout::class)->floors()->all();

    expect($ground->tables->pluck('name'))->toContain('VIP', 'Loose')->not->toContain('S1', 'T1')
        ->and($second->tables->pluck('name')->all())->toEqualCanonicalizing(
            ['L1', 'L2', 'L3', 'L4', 'C1', 'C2', 'C3', 'U1', 'U2', 'S1', 'S2', 'S3', 'S4', 'K1', 'K2']
        );

    $client = loungeUser('client');

    $this->actingAs($client)->get(route('client.reservations.index'))
        ->assertOk()
        ->assertViewHas('activeFloorId', $ground->id)
        ->assertSee('Karaoke room')
        ->assertSee('data-floor-tab="'.$second->id.'"', false);

    $this->actingAs($client)->get(route('client.reservations.index', ['floor' => $second->id]))
        ->assertViewHas('activeFloorId', $second->id);

    $this->actingAs(loungeUser('staff'))->get(route('staff.orders.create', ['floor' => 'nope']))
        ->assertOk()
        ->assertViewHas('activeFloorId', $ground->id);
});

test('pages with the plan still work before any floor exists', function () {
    Table::create(['name' => 'A1', 'capacity' => 4]);

    $this->actingAs(loungeUser('client'))->get(route('client.reservations.index'))->assertOk()->assertSee('A1');
    $this->actingAs(loungeUser('staff'))->get(route('staff.reservations.index'))->assertOk()->assertSee('A1');
    $this->actingAs(loungeUser('admin'))->get(route('admin.tables.index'))->assertOk()->assertSee('A1');
});
