<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceProvider;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;

class ServiceProviderSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        
        // Names for providers
        $names = [
            'Emmanuel Okonkwo', 'Chidi Adeleke', 'Amaka Nwosu', 'Tunde Bakare', 'Ngozi Eze',
        ];

        foreach ($categories as $index => $category) {
            foreach ($names as $key => $name) {
                $providerNumber = ($index * 5) + $key + 1;
                
                ServiceProvider::create([
                    'full_name' => $name,
                    'email' => strtolower(str_replace(' ', '.', $name)) . $providerNumber . '@provider.com',
                    'phone_number' => '+234801234' . str_pad($providerNumber, 4, '0', STR_PAD_LEFT),
                    'password' => Hash::make('password'),
                    'category_id' => $category->id,
                    // Asaba region coordinates with variation
                    'latitude' => 6.1950 + (rand(-100, 100) / 1000),
                    'longitude' => 6.6981 + (rand(-100, 100) / 1000),
                    'experience_years' => rand(1, 15),
                    'rating_avg' => rand(30, 50) / 10, // 3.0 to 5.0
                    'is_verified' => rand(0, 1) === 1,
                    'phone_verified' => true,
                ]);
            }
        }
    }
}
