<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\ServiceProvider;
use App\Models\User;
use App\Models\ContactRecord;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_user_can_create_review_after_contact()
    {
        $user = User::factory()->create(['phone_verified' => true]);
        $provider = ServiceProvider::where('is_verified', true)->first();

        // Create contact record first
        ContactRecord::create([
            'user_id' => $user->id,
            'provider_id' => $provider->id,
        ]);

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/reviews', [
                'provider_id' => $provider->id,
                'rating' => 5,
                'comment' => 'Excellent service!',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'review' => ['id', 'rating', 'comment']
            ]);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'provider_id' => $provider->id,
            'rating' => 5,
        ]);
    }

    public function test_user_cannot_review_without_contact()
    {
        $user = User::factory()->create(['phone_verified' => true]);
        $provider = ServiceProvider::where('is_verified', true)->first();

        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/reviews', [
                'provider_id' => $provider->id,
                'rating' => 5,
                'comment' => 'Excellent service!',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'You must contact the provider before leaving a review'
            ]);
    }

    public function test_user_cannot_review_same_provider_twice()
    {
        $user = User::factory()->create(['phone_verified' => true]);
        $provider = ServiceProvider::where('is_verified', true)->first();

        ContactRecord::create([
            'user_id' => $user->id,
            'provider_id' => $provider->id,
        ]);

        $token = JWTAuth::fromUser($user);

        // First review
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/reviews', [
                'provider_id' => $provider->id,
                'rating' => 5,
                'comment' => 'Great!',
            ]);

        // Second review attempt
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/reviews', [
                'provider_id' => $provider->id,
                'rating' => 4,
                'comment' => 'Good!',
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'error' => 'You have already reviewed this provider'
            ]);
    }

    public function test_provider_rating_updates_after_review()
    {
        $user = User::factory()->create(['phone_verified' => true]);
        $provider = ServiceProvider::factory()->create([
            'is_verified' => true,
            'phone_verified' => true,
            'category_id' => 1,
            'rating_avg' => 0,
        ]);

        ContactRecord::create([
            'user_id' => $user->id,
            'provider_id' => $provider->id,
        ]);

        $token = JWTAuth::fromUser($user);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/reviews', [
                'provider_id' => $provider->id,
                'rating' => 4,
                'comment' => 'Good service',
            ]);

        $provider->refresh();
        $this->assertEquals(4.0, $provider->rating_avg);
    }
}
