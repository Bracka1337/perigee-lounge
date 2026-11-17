<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@perigee.test'],
            [
                'name' => 'PERIGEE Admin',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff@perigee.test'],
            [
                'name' => 'PERIGEE Staff',
                'password' => Hash::make('password'),
                'role' => User::ROLE_STAFF,
                'email_verified_at' => now(),
            ]
        );

        // A demo guest, so the client side can be tried without registering.
        // email_verified_at is not fillable, so it is set with forceFill.
        $client = User::updateOrCreate(
            ['email' => 'client@perigee.test'],
            [
                'name' => 'Demo Guest',
                'password' => Hash::make('password'),
                'role' => User::ROLE_CLIENT,
            ]
        );
        $client->forceFill(['email_verified_at' => $client->email_verified_at ?? now()])->save();
    }
}
