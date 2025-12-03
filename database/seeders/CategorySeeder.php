<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Plumber', 'slug' => 'plumber'],
            ['name' => 'Carpenter', 'slug' => 'carpenter'],
            ['name' => 'Gardener', 'slug' => 'gardener'],
            ['name' => 'Fumigation', 'slug' => 'fumigation'],
            ['name' => 'Catering', 'slug' => 'catering'],
            ['name' => 'Cleaner', 'slug' => 'cleaner'],
            ['name' => 'Electrician', 'slug' => 'electrician'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
