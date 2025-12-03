<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => '+234' . fake()->unique()->numerify('80########'),
            'password' => Hash::make('password'),
            'latitude' => fake()->latitude(6.1, 6.3),
            'longitude' => fake()->longitude(6.6, 6.8),
            'phone_verified' => false,
        ];
    }
}
