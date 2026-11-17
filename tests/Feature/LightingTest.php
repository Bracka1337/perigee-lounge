<?php

use App\Enums\RoomMood;
use App\Models\Fixture;
use App\Models\Floor;
use App\Models\Room;
use App\Models\User;
use App\Services\LoungeLayout;
use Database\Seeders\DefaultTranslationsSeeder;
use Database\Seeders\TableSeeder;

function lightingAdmin(): User
{
    $user = User::factory()->create();
    $user->forceFill(['role' => 'admin', 'email_verified_at' => now()])->save();

    return $user;
}

function lightingFloor(): array
{
    $floor = Floor::create(['name' => ['en' => 'Ground floor'], 'sort_order' => 0]);
    $room = Room::create(['floor_id' => $floor->id, 'name' => ['en' => 'Hall'], 'kind' => 'seating', 'color' => 'violet', 'x' => 0, 'y' => 0, 'width' => 10, 'height' => 8]);

    return [$floor, $room];
}

function lightingPlan(Room $room, array $roomChanges = [], array $fixtures = []): array
{
    return [
        'rooms' => [[
            'id' => $room->id, 'name' => $room->name, 'kind' => 'seating', 'color' => 'violet',
            'x' => 0, 'y' => 0, 'width' => 10, 'height' => 8, ...$roomChanges,
        ]],
        'tables' => [],
        'fixtures' => $fixtures,
    ];
}

function saveLighting(Floor $floor, array $plan)
{
    return test()->actingAs(lightingAdmin())
        ->put(route('admin.floors.plan.update', ['floor' => $floor]), ['plan_payload' => json_encode($plan)]);
}

beforeEach(function () {
    $this->seed(DefaultTranslationsSeeder::class);
});

test('a room keeps the light mood chosen on the floor plan', function () {
    [$floor, $room] = lightingFloor();

    saveLighting($floor, lightingPlan($room, ['mood' => 'cinema']))->assertSessionHasNoErrors();
    expect($room->refresh()->mood)->toBe(RoomMood::Cinema);

    // A plan from before moods existed leaves the mood alone.
    saveLighting($floor, lightingPlan($room))->assertSessionHasNoErrors();
    expect($room->refresh()->mood)->toBe(RoomMood::Cinema);

    saveLighting($floor, lightingPlan($room, ['mood' => 'disco']))->assertSessionHasErrors();
    expect($room->refresh()->mood)->toBe(RoomMood::Cinema);
});

test('lights keep their colour and strength, other fixtures keep no settings', function () {
    [$floor, $room] = lightingFloor();

    saveLighting($floor, lightingPlan($room, [], [
        ['type' => 'pendant_light', 'x' => 3, 'y' => 3, 'rotation' => 0, 'width' => 0.5, 'depth' => 0.5, 'settings' => ['color' => 'rose', 'strength' => 8]],
        ['type' => 'floor_lamp', 'x' => 5, 'y' => 3, 'rotation' => 0, 'width' => 0.5, 'depth' => 0.5],
        ['type' => 'plant', 'x' => 7, 'y' => 3, 'rotation' => 0, 'width' => 0.7, 'depth' => 0.7, 'settings' => ['color' => 'teal', 'strength' => 3]],
    ]))->assertSessionHasNoErrors();

    expect(Fixture::where('type', 'pendant_light')->value('settings'))->toBe(['color' => 'rose', 'strength' => 8])
        ->and(Fixture::where('type', 'floor_lamp')->firstOrFail()->settings)->toBe(['color' => 'amber', 'strength' => 5])
        ->and(Fixture::where('type', 'plant')->firstOrFail()->settings)->toBeNull();
});

test('light settings are checked', function (array $settings) {
    [$floor, $room] = lightingFloor();

    saveLighting($floor, lightingPlan($room, [], [
        ['type' => 'neon_strip', 'x' => 3, 'y' => 3, 'rotation' => 0, 'width' => 2, 'depth' => 0.1, 'settings' => $settings],
    ]))->assertSessionHasErrors();

    expect(Fixture::count())->toBe(0);
})->with([
    'unknown colour' => [['color' => 'ultraviolet', 'strength' => 5]],
    'too strong' => [['color' => 'amber', 'strength' => 11]],
    'too weak' => [['color' => 'amber', 'strength' => 0]],
    'unknown setting' => [['color' => 'amber', 'blink' => true]],
]);

test('the seeded lounge has moods and lights, and the 3D data carries them', function () {
    $this->seed(TableSeeder::class);

    expect(Room::where('name->en', 'Cinema room')->firstOrFail()->mood)->toBe(RoomMood::Cinema)
        ->and(Room::where('name->en', 'Main hall')->firstOrFail()->mood)->toBe(RoomMood::Warm)
        ->and(Fixture::whereIn('type', ['pendant_light', 'floor_lamp', 'neon_strip', 'led_strip'])->count())->toBe(23);

    $layout = app(LoungeLayout::class);
    [$ground] = $layout->toArray($layout->floors())['floors'];
    $pendant = collect($ground['fixtures'])->firstWhere('type', 'pendant_light');

    expect(collect($ground['rooms'])->firstWhere('name', 'Game room')['mood'])->toBe('neon')
        ->and($pendant['settings'])->toBe(['color' => 'amber', 'strength' => 6]);
});

test('lights stay off the booking plan', function () {
    $this->seed(TableSeeder::class);
    $client = User::factory()->create();
    $client->forceFill(['role' => 'client', 'email_verified_at' => now()])->save();

    $html = (string) $this->actingAs($client)->get(route('client.reservations.index'))->assertOk()->getContent();

    expect($html)->not->toContain('plan-fixture--lights')->and($html)->toContain('plan-fixture--entertainment');
});

test('existing lounges get the default moods and lights once', function () {
    $this->seed(TableSeeder::class);
    Fixture::whereIn('type', ['pendant_light', 'floor_lamp', 'neon_strip', 'led_strip'])->delete();
    // An admin already lit the ground floor their own way.
    $ground = Floor::where('name->en', 'Ground floor')->firstOrFail();
    Fixture::create(['floor_id' => $ground->id, 'type' => 'floor_lamp', 'x' => 10, 'y' => 5, 'width' => 0.5, 'depth' => 0.5, 'settings' => ['color' => 'teal', 'strength' => 2]]);

    $migration = require database_path('migrations/2026_11_17_130000_add_mood_to_rooms_and_default_lights.php');
    Schema::table('rooms', fn ($table) => $table->dropColumn('mood'));
    $migration->up();

    expect(Room::where('name->en', 'Cinema room')->firstOrFail()->mood)->toBe(RoomMood::Cinema)
        ->and(Room::where('name->en', 'Karaoke room')->firstOrFail()->mood)->toBe(RoomMood::Neon)
        ->and($ground->fixtures()->where('type', 'floor_lamp')->count())->toBe(1)
        ->and(Floor::where('name->en', 'Second floor')->firstOrFail()->fixtures()->whereIn('type', ['pendant_light', 'floor_lamp', 'neon_strip', 'led_strip'])->count())->toBe(12);
});
