<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\ServiceProvider;
use App\Services\SmsService;
use Illuminate\Support\Facades\Hash;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\CategorySeeder::class);
    }

    public function test_user_can_register()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'full_name' => 'Test User',
            'email' => 'test@example.com',
            'phone_number' => '+2348099999999',
            'password' => 'password123',
            'latitude' => 6.1950,
            'longitude' => 6.6981,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'full_name', 'email', 'phone_number']
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'phone_number' => '+2348099999999',
        ]);
    }

    public function test_provider_can_register()
    {
        $response = $this->postJson('/api/v1/auth/register-provider', [
            'full_name' => 'Test Provider',
            'email' => 'provider@example.com',
            'phone_number' => '+2348088888888',
            'password' => 'password123',
            'category_id' => 1,
            'experience_years' => 5,
            'latitude' => 6.1950,
            'longitude' => 6.6981,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'provider'
            ]);

        $this->assertDatabaseHas('service_providers', [
            'email' => 'provider@example.com',
        ]);
    }

    public function test_otp_can_be_requested()
    {
        $user = User::factory()->create([
            'phone_number' => '+2348077777777',
            'phone_verified' => false,
        ]);

        $response = $this->postJson('/api/v1/auth/request-otp', [
            'phone_number' => '+2348077777777',
            'user_type' => 'user',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'OTP sent successfully'
            ]);
    }

    public function test_otp_can_be_verified()
    {
        $user = User::factory()->create([
            'phone_number' => '+2348066666666',
            'phone_verified' => false,
        ]);

        // Generate OTP
        $smsService = app(SmsService::class);
        $otp = $smsService->generateOtp('+2348066666666');

        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'phone_number' => '+2348066666666',
            'otp' => $otp,
            'user_type' => 'user',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'access_token',
                'user'
            ]);

        $this->assertDatabaseHas('users', [
            'phone_number' => '+2348066666666',
            'phone_verified' => true,
        ]);
    }

    public function test_user_can_login_after_verification()
    {
        $user = User::factory()->create([
            'email' => 'verified@example.com',
            'password' => Hash::make('password'),
            'phone_verified' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'verified@example.com',
            'password' => 'password',
            'user_type' => 'user',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'user'
            ]);
    }

    public function test_unverified_user_cannot_login()
    {
        $user = User::factory()->create([
            'email' => 'unverified@example.com',
            'password' => Hash::make('password'),
            'phone_verified' => false,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'unverified@example.com',
            'password' => 'password',
            'user_type' => 'user',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'error' => 'Please verify your phone number first'
            ]);
    }
}
