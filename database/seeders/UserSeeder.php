<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Asaba coordinates approximately: 6.2°N, 6.7°E
        $users = [
            [
                'full_name' => 'John Doe',
                'email' => 'john@example.com',
                'phone_number' => '+2348012345671',
                'password' => Hash::make('password'),
                'latitude' => 6.1950,
                'longitude' => 6.6981,
                'phone_verified' => true,
            ],
            [
                'full_name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'phone_number' => '+2348012345672',
                'password' => Hash::make('password'),
                'latitude' => 6.2050,
                'longitude' => 6.7081,
                'phone_verified' => true,
            ],
            [
                'full_name' => 'Mike Johnson',
                'email' => 'mike@example.com',
                'phone_number' => '+2348012345673',
                'password' => Hash::make('password'),
                'latitude' => 6.1850,
                'longitude' => 6.6881,
                'phone_verified' => true,
            ],
            [
                'full_name' => 'Sarah Williams',
                'email' => 'sarah@example.com',
                'phone_number' => '+2348012345674',
                'password' => Hash::make('password'),
                'latitude' => 6.2150,
                'longitude' => 6.7181,
                'phone_verified' => true,
            ],
            [
                'full_name' => 'David Brown',
                'email' => 'david@example.com',
                'phone_number' => '+2348012345675',
                'password' => Hash::make('password'),
                'latitude' => 6.1750,
                'longitude' => 6.6781,
                'phone_verified' => true,
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
    }
}
