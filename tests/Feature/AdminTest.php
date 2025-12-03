<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\ServiceProvider;
use App\Models\ProviderVerification;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_admin_can_get_pending_verifications()
    {
        $admin = Admin::first();
        $token = JWTAuth::fromUser($admin);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/admin/verifications');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ]);
    }

    public function test_admin_can_approve_provider()
    {
        $admin = Admin::first();
        $provider = ServiceProvider::factory()->create([
            'is_verified' => false,
            'phone_verified' => true,
            'category_id' => 1,
        ]);

        $verification = ProviderVerification::create([
            'provider_id' => $provider->id,
            'document_url' => 'https://example.com/doc.jpg',
            'status' => 'pending',
        ]);

        $token = JWTAuth::fromUser($admin);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/admin/verifications/{$verification->id}", [
                'status' => 'approved',
                'notes' => 'Documents verified',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('provider_verifications', [
            'id' => $verification->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('service_providers', [
            'id' => $provider->id,
            'is_verified' => true,
        ]);
    }

    public function test_admin_can_reject_provider()
    {
        $admin = Admin::first();
        $provider = ServiceProvider::factory()->create([
            'is_verified' => false,
            'phone_verified' => true,
            'category_id' => 1,
        ]);

        $verification = ProviderVerification::create([
            'provider_id' => $provider->id,
            'document_url' => 'https://example.com/doc.jpg',
            'status' => 'pending',
        ]);

        $token = JWTAuth::fromUser($admin);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/admin/verifications/{$verification->id}", [
                'status' => 'rejected',
                'notes' => 'Invalid documents',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('provider_verifications', [
            'id' => $verification->id,
            'status' => 'rejected',
        ]);

        $provider->refresh();
        $this->assertFalse($provider->is_verified);
    }

    public function test_non_admin_cannot_access_admin_routes()
    {
        $user = \App\Models\User::factory()->create(['phone_verified' => true]);
        $token = JWTAuth::fromUser($user);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/admin/verifications');

        $response->assertStatus(403);
    }
}
