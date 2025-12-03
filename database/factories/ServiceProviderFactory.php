<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use App\Models\Category;

class ServiceProviderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => '+234' . fake()->unique()->numerify('80########'),
            'password' => Hash::make('password'),
            'category_id' => Category::factory(),
            'latitude' => fake()->latitude(6.1, 6.3),
            'longitude' => fake()->longitude(6.6, 6.8),
            'experience_years' => fake()->numberBetween(1, 20),
            'rating_avg' => fake()->randomFloat(2, 0, 5),
            'is_verified' => false,
            'phone_verified' => false,
        ];
    }
}
