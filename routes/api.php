<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProviderController;
use App\Http\Controllers\Api\ProviderPortfolioController;
use App\Http\Controllers\Api\ProviderVerificationController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ContactRecordController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\CategoryController;

/*
|--------------------------------------------------------------------------
| API Routes (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    
    // Public routes
    Route::get('/health', function () {
        return response()->json(['status' => 'ok']);
    });

    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/register-provider', [AuthController::class, 'registerProvider']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/request-otp', [AuthController::class, 'requestOtp']);
        Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
        
        Route::middleware('auth:api')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });
    });

    // Categories
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{slug}', [CategoryController::class, 'show']);

    // Providers (public)
    Route::get('/providers', [ProviderController::class, 'index']);
    Route::get('/providers/search', [ProviderController::class, 'search']);
    Route::get('/providers/{id}', [ProviderController::class, 'show']);

    // Reviews
    Route::get('/providers/{providerId}/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews', [ReviewController::class, 'store']);

    // Contact records (authenticated users only)
    Route::middleware('auth:api')->group(function () {
        Route::post('/contact-records', [ContactRecordController::class, 'store']);
        Route::get('/contact-records/check', [ContactRecordController::class, 'check']);
    });

    // Provider portfolio (authenticated providers only)
    Route::post('/portfolios', [ProviderPortfolioController::class, 'store']);
    Route::delete('/portfolios/{id}', [ProviderPortfolioController::class, 'destroy']);

    // Provider verification (authenticated providers only)
    Route::post('/verifications', [ProviderVerificationController::class, 'store']);

    // Blog (public read, admin write)
    Route::get('/blog', [BlogController::class, 'index']);
    Route::get('/blog/{slug}', [BlogController::class, 'show']);
    
    Route::middleware(['auth:api', 'admin'])->prefix('admin/blog')->group(function () {
        Route::get('/', [BlogController::class, 'adminIndex']);
        Route::post('/', [BlogController::class, 'store']);
        Route::put('/{id}', [BlogController::class, 'update']);
        Route::delete('/{id}', [BlogController::class, 'destroy']);
    });

    // Admin routes
    Route::middleware(['auth:api', 'admin'])->prefix('admin')->group(function () {
        Route::get('/verifications', [AdminController::class, 'getVerifications']);
        Route::get('/verifications/pending', [AdminController::class, 'getPendingVerifications']);
        Route::put('/verifications/{id}', [AdminController::class, 'updateVerification']);
        
        Route::get('/categories', [AdminController::class, 'getCategories']);
        Route::post('/categories', [AdminController::class, 'createCategory']);
        
        Route::get('/users', [AdminController::class, 'getUsers']);
        Route::get('/providers', [AdminController::class, 'getProviders']);
        
        Route::get('/reviews', [AdminController::class, 'getReviews']);
        Route::delete('/reviews/{id}', [AdminController::class, 'deleteReview']);
    });
});
