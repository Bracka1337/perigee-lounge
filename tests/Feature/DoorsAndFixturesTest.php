<?php

use App\Enums\DoorKind;
use App\Models\Door;
use App\Models\Fixture;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Table;
use App\Models\User;
use App\Services\LoungeLayout;
use Database\Seeders\DefaultTranslationsSeeder;
use Database\Seeders\TableSeeder;
use Illuminate\Support\Facades\DB;

function furnishAdmin(): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin', 'email_verified_at' => now()])->save();

    return $user;
}

/**
 * A floor with two rooms side by side: Hall (0-10 m) and Bar (10-16 m), both 8 m deep.
 */
function furnishFloor(): Floor
{
    $floor = Floor::create(['name' => ['en' => 'Ground floor', 'lv' => 'G', 'ru' => 'G'], 'sort_order' => 0]);
    Room::create(['floor_id' => $floor->id, 'name' => ['en' => 'Hall'], 'kind' => 'seating', 'color' => 'violet', 'x' => 0, 'y' => 0, 'width' => 10, 'height' => 8]);
    Room::create(['floor_id' => $floor->id, 'name' => ['en' => 'Bar'], 'kind' => 'bar', 'color' => 'amber', 'x' => 10, 'y' => 0, 'width' => 6, 'height' => 8]);

    return $floor;
}

/**
 * @return array<string, mixed>
 */
function furnishPlan(Floor $floor, array $doors, array $fixtures): array
{
    return [
        'rooms' => $floor->rooms()->get()->map(fn (Room $room) => [
            'id' => $room->id, 'name' => $room->name, 'kind' => $room->kind->value, 'color' => $room->color->value,
            'x' => $room->x, 'y' => $room->y, 'width' => $room->width, 'height' => $room->height,
        ])->all(),
        'tables' => [],
        'doors' => $doors,
        'fixtures' => $fixtures,
    ];
}

function saveFurnished(Floor $floor, array $plan)
{
    return test()->actingAs(furnishAdmin())
        ->put(route('admin.floors.plan.update', ['floor' => $floor]), ['plan_payload' => json_encode($plan)]);
}

beforeEach(function () {
    $this->seed(DefaultTranslationsSeeder::class);
});

test('seeding gives every room a door and furnishes both floors', function () {
    $this->seed(TableSeeder::class);

    expect(Door::count())->toBe(21)
        ->and(Fixture::count())->toBe(30)
        ->and(Fixture::where('type', 'bar_counter')->get()->pluck('points')->map(fn ($points) => count($points))->all())->toBe([3, 2])
        ->and(Fixture::where('type', 'game_station')->count())->toBe(2);

    // Every room that is not a corridor has at least one door in one of its walls.
    foreach (Room::with('floor.doors')->where('kind', '!=', 'corridor')->get() as $room) {
        $doors = $room->floor->doors->filter(fn (Door $door) => $door->orientation === 'h'
            ? (abs($door->y - $room->y) < 0.15 || abs($door->y - $room->y - $room->height) < 0.15) && $door->x > $room->x && $door->x < $room->x + $room->width
            : (abs($door->x - $room->x) < 0.15 || abs($door->x - $room->x - $room->width) < 0.15) && $door->y > $room->y && $door->y < $room->y + $room->height);

        expect($doors)->not->toBeEmpty("{$room->t('name')} has no door");
    }
});

test('the floor plan adds, changes and removes doors and fixtures', function () {
    $floor = furnishFloor();
    $old = Door::create(['floor_id' => $floor->id, 'kind' => 'single', 'orientation' => 'v', 'x' => 10, 'y' => 2, 'width' => 0.9]);
    $plant = Fixture::create(['floor_id' => $floor->id, 'type' => 'plant', 'x' => 1, 'y' => 1, 'width' => 0.7, 'depth' => 0.7]);

    saveFurnished($floor, furnishPlan($floor, [
        ['id' => $old->id, 'kind' => 'double', 'orientation' => 'v', 'x' => 10, 'y' => 5, 'width' => 1.6],
        ['kind' => 'opening', 'orientation' => 'h', 'x' => 5, 'y' => 8, 'width' => 1.8],
    ], [
        ['type' => 'game_station', 'x' => 5, 'y' => 0.5, 'rotation' => 0, 'width' => 1.8, 'depth' => 0.6],
        ['type' => 'bar_counter', 'x' => 13, 'y' => 2, 'rotation' => 0, 'width' => 0.6, 'depth' => 0.6, 'points' => [[11, 1.5], [15, 1.5], [15, 4]]],
    ]))->assertSessionHasNoErrors();

    expect($old->refresh()->only(['kind', 'y', 'width']))->toEqual(['kind' => DoorKind::Double, 'y' => 5.0, 'width' => 1.6])
        ->and(Door::count())->toBe(2)
        ->and(Fixture::find($plant->id))->toBeNull()
        ->and(Fixture::where('type', 'bar_counter')->firstOrFail()->points)->toBe([[11, 1.5], [15, 1.5], [15, 4]]);
});

test('a plan without doors and fixtures leaves them as they are', function () {
    $floor = furnishFloor();
    Door::create(['floor_id' => $floor->id, 'kind' => 'single', 'orientation' => 'v', 'x' => 10, 'y' => 2, 'width' => 0.9]);

    $plan = furnishPlan($floor, [], []);
    unset($plan['doors'], $plan['fixtures']);
    saveFurnished($floor, $plan)->assertSessionHasNoErrors();

    expect(Door::count())->toBe(1);
});

test('doors must sit on a wall and fixtures inside a room', function (array $doors, array $fixtures) {
    $floor = furnishFloor();

    saveFurnished($floor, furnishPlan($floor, $doors, $fixtures))->assertSessionHasErrors();

    expect(Door::count())->toBe(0)->and(Fixture::count())->toBe(0);
})->with([
    'door in the middle of a room' => [[['kind' => 'single', 'orientation' => 'v', 'x' => 5, 'y' => 4, 'width' => 0.9]], []],
    'door wider than its wall' => [[['kind' => 'double', 'orientation' => 'h', 'x' => 15.5, 'y' => 8, 'width' => 1.6]], []],
    'door too narrow' => [[['kind' => 'single', 'orientation' => 'v', 'x' => 10, 'y' => 4, 'width' => 0.2]], []],
    'unknown door kind' => [[['kind' => 'portal', 'orientation' => 'v', 'x' => 10, 'y' => 4, 'width' => 0.9]], []],
    'fixture outside' => [[], [['type' => 'plant', 'x' => 30, 'y' => 30, 'rotation' => 0, 'width' => 0.7, 'depth' => 0.7]]],
    'bar corner outside' => [[], [['type' => 'bar_counter', 'x' => 13, 'y' => 2, 'rotation' => 0, 'width' => 0.6, 'depth' => 0.6, 'points' => [[11, 1], [20, 1]]]]],
    'bar with one corner' => [[], [['type' => 'bar_counter', 'x' => 13, 'y' => 2, 'rotation' => 0, 'width' => 0.6, 'depth' => 0.6, 'points' => [[11, 1]]]]],
    'unknown fixture' => [[], [['type' => 'jacuzzi', 'x' => 5, 'y' => 4, 'rotation' => 0, 'width' => 2, 'depth' => 2]]],
]);

test('a door or fixture of another floor cannot be changed through this floor', function () {
    $floor = furnishFloor();
    $other = Floor::create(['name' => ['en' => 'Roof'], 'sort_order' => 1]);
    $foreign = Door::create(['floor_id' => $other->id, 'kind' => 'single', 'orientation' => 'v', 'x' => 10, 'y' => 2, 'width' => 0.9]);

    saveFurnished($floor, furnishPlan($floor, [['id' => $foreign->id, 'kind' => 'double', 'orientation' => 'v', 'x' => 10, 'y' => 4, 'width' => 1.6]], []))
        ->assertSessionHasErrors('doors.0.id');

    expect($foreign->refresh()->floor_id)->toBe($other->id);
});

test('the plans and the 3D data include doors and fixtures', function () {
    $this->seed(TableSeeder::class);
    $client = User::factory()->create();
    $client->forceFill(['role' => 'client', 'email_verified_at' => now()])->save();

    $layout = app(LoungeLayout::class);
    [$ground] = $layout->toArray($layout->floors())['floors'];
    expect($ground['doors'])->toHaveCount(11)
        ->and(collect($ground['fixtures'])->firstWhere('type', 'bar_counter')['points'])->toHaveCount(3);

    $this->actingAs($client)->get(route('client.reservations.index'))
        ->assertOk()
        ->assertSee('class="plan-door-gap"', false)
        ->assertSee('class="plan-bar"', false);
});

test('existing lounges get the cabin corridor, doors and fixtures once', function () {
    $this->seed(TableSeeder::class);
    $second = Floor::where('name->en', 'Second floor')->firstOrFail();

    // Put the lounge back the way the previous layout drew it: no doors, no fixtures, old cabins.
    Door::query()->delete();
    Fixture::query()->delete();
    Room::where('name->en', 'Cabin corridor')->delete();
    foreach (['Cabin 1' => [21, 6.5], 'Cabin 2' => [25.5, 6.5], 'Cabin 3' => [21, 13.75], 'Cabin 4' => [25.5, 13.75]] as $name => [$x, $y]) {
        Room::where('floor_id', $second->id)->where('name->en', $name)->update(['x' => $x, 'y' => $y, 'height' => 7.25]);
    }
    // An admin already furnished the ground floor their own way.
    $ground = Floor::where('name->en', 'Ground floor')->firstOrFail();
    Fixture::create(['floor_id' => $ground->id, 'type' => 'aquarium', 'x' => 12, 'y' => 5, 'width' => 2, 'depth' => 0.6]);

    $migration = require database_path('migrations/2026_11_17_120100_furnish_default_lounge_layout.php');
    $migration->up();
    $migration->up();

    $cabin = Room::where('floor_id', $second->id)->where('name->en', 'Cabin 3')->firstOrFail();
    expect(Room::where('name->en', 'Cabin corridor')->count())->toBe(1)
        ->and([$cabin->y, $cabin->height])->toBe([14.75, 6.25])
        ->and(Table::where('name', 'S3')->firstOrFail()->pos_y)->toBe(17.88)
        ->and($second->doors()->count())->toBe(10)
        ->and($ground->fixtures()->pluck('type')->map->value->all())->toBe(['aquarium'])
        ->and($ground->doors()->count())->toBe(0);
});

test('a fresh database is left to the seeders', function () {
    (require database_path('migrations/2026_11_17_120100_furnish_default_lounge_layout.php'))->up();

    expect(DB::table('doors')->count())->toBe(0)->and(DB::table('fixtures')->count())->toBe(0);
});
