<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/ru/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/ru/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'birth_date' => now()->subYears(25)->format('Y-m-d'),
        'terms' => '1',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});
