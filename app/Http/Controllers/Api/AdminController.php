<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceProvider;
use App\Models\ProviderVerification;
use App\Models\Category;
use App\Models\User;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('admin');
    }

    /**
     * Get all provider verifications
     */
    public function getVerifications()
    {
        $verifications = ProviderVerification::with('provider.category')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($verifications);
    }

    /**
     * Get pending provider verifications
     */
    public function getPendingVerifications()
    {
        $verifications = ProviderVerification::with('provider.category')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($verifications);
    }

    /**
     * Approve or reject provider verification
     */
    public function updateVerification(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $verification = ProviderVerification::with('provider')->findOrFail($id);
        $verification->status = $request->status;
        $verification->notes = $request->notes;
        $verification->save();

        // Update provider's verification status
        if ($request->status === 'approved') {
            $provider = $verification->provider;
            $provider->is_verified = true;
            $provider->save();

            // Send email notification to provider (placeholder)
            // Mail::to($provider->email)->send(new ProviderVerified($provider));
        }

        return response()->json([
            'message' => "Provider verification {$request->status}",
            'verification' => $verification
        ]);
    }

    /**
     * Get all categories
     */
    public function getCategories()
    {
        $categories = Category::withCount('providers')->get();
        return response()->json($categories);
    }

    /**
     * Create a new category
     */
    public function createCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:categories',
            'slug' => 'required|string|unique:categories',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $category = Category::create($request->only(['name', 'slug']));

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category
        ], 201);
    }

    /**
     * Get all users with pagination
     */
    public function getUsers(Request $request)
    {
        $users = User::withCount('reviews')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($users);
    }

    /**
     * Get all providers with pagination
     */
    public function getProviders(Request $request)
    {
        $providers = ServiceProvider::with('category')
            ->withCount('reviews')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($providers);
    }

    /**
     * Get all reviews with moderation
     */
    public function getReviews(Request $request)
    {
        $reviews = Review::with(['user', 'provider'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($reviews);
    }

    /**
     * Delete a review (moderation)
     */
    public function deleteReview($id)
    {
        $review = Review::findOrFail($id);
        $providerId = $review->provider_id;
        $review->delete();

        // Update provider's rating
        $provider = ServiceProvider::find($providerId);
        $provider->updateRating();

        return response()->json(['message' => 'Review deleted successfully']);
    }
}
