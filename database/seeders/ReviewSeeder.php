<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\User;
use App\Models\ServiceProvider;
use App\Models\ContactRecord;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $providers = ServiceProvider::where('is_verified', true)->get();

        $comments = [
            'Excellent service! Very professional and punctual.',
            'Good work, would recommend to others.',
            'Average service, could be better.',
            'Outstanding work! Exceeded my expectations.',
            'Quick and efficient. Very satisfied.',
            'Professional and courteous. Great experience.',
        ];

        foreach ($providers as $provider) {
            // Create 1-2 reviews per verified provider
            $reviewCount = rand(1, 2);
            $selectedUsers = $users->random(min($reviewCount, $users->count()));

            foreach ($selectedUsers as $user) {
                // Create contact record first
                ContactRecord::create([
                    'user_id' => $user->id,
                    'provider_id' => $provider->id,
                ]);

                // Create review
                Review::create([
                    'user_id' => $user->id,
                    'provider_id' => $provider->id,
                    'rating' => rand(3, 5),
                    'comment' => $comments[array_rand($comments)],
                ]);
            }

            // Update provider's rating
            $provider->updateRating();
        }
    }
}
