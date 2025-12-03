<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ServiceProvider;
use App\Models\Admin;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    protected $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|unique:users',
            'password' => 'required|string|min:6',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return response()->json([
            'message' => 'User registered successfully. Please verify your phone number.',
            'user' => $user
        ], 201);
    }

    /**
     * Register a new service provider
     */
    public function registerProvider(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:service_providers',
            'phone_number' => 'required|string|unique:service_providers',
            'password' => 'required|string|min:6',
            'category_id' => 'required|exists:categories,id',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'experience_years' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $provider = ServiceProvider::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone_number' => $request->phone_number,
            'password' => Hash::make($request->password),
            'category_id' => $request->category_id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'experience_years' => $request->experience_years,
        ]);

        // Notify admin (placeholder - implement actual email notification)
        // Mail::to(config('mail.admin_email'))->send(new ProviderRegistered($provider));

        return response()->json([
            'message' => 'Provider registered successfully. Please verify your phone number and upload verification documents.',
            'provider' => $provider
        ], 201);
    }

    /**
     * Login user, provider, or admin
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'user_type' => 'required|in:user,provider,admin',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $credentials = $request->only('email', 'password');
        $userType = $request->user_type;

        // Determine which model to use
        $model = match($userType) {
            'user' => User::class,
            'provider' => ServiceProvider::class,
            'admin' => Admin::class,
        };

        $user = $model::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // For users and providers, check phone verification
        if (in_array($userType, ['user', 'provider']) && !$user->phone_verified) {
            return response()->json(['error' => 'Please verify your phone number first'], 403);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => $user,
            'user_type' => $userType
        ]);
    }

    /**
     * Request OTP for phone verification
     */
    public function requestOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string',
            'user_type' => 'required|in:user,provider',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $model = $request->user_type === 'user' ? User::class : ServiceProvider::class;
        $user = $model::where('phone_number', $request->phone_number)->first();

        if (!$user) {
            return response()->json(['error' => 'Phone number not registered'], 404);
        }

        $otp = $this->smsService->generateOtp($request->phone_number);
        $this->smsService->sendOtp($request->phone_number, $otp);

        return response()->json([
            'message' => 'OTP sent successfully',
            'expires_in' => '5 minutes'
        ]);
    }

    /**
     * Verify OTP and mark phone as verified
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|string',
            'otp' => 'required|string|size:6',
            'user_type' => 'required|in:user,provider',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if (!$this->smsService->verifyOtp($request->phone_number, $request->otp)) {
            return response()->json(['error' => 'Invalid or expired OTP'], 400);
        }

        $model = $request->user_type === 'user' ? User::class : ServiceProvider::class;
        $user = $model::where('phone_number', $request->phone_number)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->phone_verified = true;
        $user->save();

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Phone verified successfully',
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => $user,
            'user_type' => $request->user_type
        ]);
    }

    /**
     * Logout user
     */
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Get authenticated user
     */
    public function me()
    {
        return response()->json(auth()->user());
    }
}
