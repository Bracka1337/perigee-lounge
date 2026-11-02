<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
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
    }
}
