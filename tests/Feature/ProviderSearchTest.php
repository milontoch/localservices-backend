<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\ServiceProvider;
use App\Models\Category;
use App\Models\Review;
use App\Models\User;

class ProviderSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_can_list_verified_providers()
    {
        $response = $this->getJson('/api/v1/providers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'full_name', 'category', 'rating_avg', 'is_verified']
                ]
            ]);

        // Ensure only verified providers are returned
        foreach ($response->json('data') as $provider) {
            $this->assertTrue($provider['is_verified']);
        }
    }

    public function test_can_filter_by_category()
    {
        $plumberCategory = Category::where('slug', 'plumber')->first();

        $response = $this->getJson('/api/v1/providers?category=plumber');

        $response->assertStatus(200);

        foreach ($response->json('data') as $provider) {
            $this->assertEquals('plumber', $provider['category']['slug']);
        }
    }

    public function test_can_search_by_distance()
    {
        // Asaba coordinates
        $lat = 6.1950;
        $lng = 6.6981;
        $radius = 10; // 10km

        $response = $this->getJson("/api/v1/providers?lat={$lat}&lng={$lng}&radius_km={$radius}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'full_name', 'distance']
                ]
            ]);

        // Verify distance is included and within radius
        foreach ($response->json('data') as $provider) {
            $this->assertLessThanOrEqual($radius, $provider['distance']);
        }
    }

    public function test_can_filter_by_minimum_rating()
    {
        $minRating = 4.0;

        $response = $this->getJson("/api/v1/providers?min_rating={$minRating}");

        $response->assertStatus(200);

        foreach ($response->json('data') as $provider) {
            $this->assertGreaterThanOrEqual($minRating, $provider['rating_avg']);
        }
    }

    public function test_can_get_single_provider()
    {
        $provider = ServiceProvider::where('is_verified', true)->first();

        $response = $this->getJson("/api/v1/providers/{$provider->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'full_name',
                'category',
                'portfolios',
                'reviews',
                'rating_avg'
            ]);
    }

    public function test_can_search_providers_by_name()
    {
        $provider = ServiceProvider::where('is_verified', true)->first();
        $searchTerm = substr($provider->full_name, 0, 5);

        $response = $this->getJson("/api/v1/providers/search?q={$searchTerm}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ]);
    }
}
