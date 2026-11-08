<?php

use App\Models\User;

test('activity logs are linked to the logged model', function () {
    $user = User::factory()->create(['name' => 'Before']);
    $user->update(['name' => 'After']);

    $logs = $user->activityLogs()->orderBy('id')->get();

    expect($logs->pluck('action')->all())->toBe(['created', 'updated'])
        ->and($logs->last()->new_values)->toBe(['name' => 'After'])
        ->and($logs->last()->subject()->first()?->is($user))->toBeTrue();
});
