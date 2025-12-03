<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ContactRecord;
use App\Models\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except(['index']);
    }

    /**
     * Get reviews for a provider
     */
    public function index($providerId)
    {
        $reviews = Review::with('user')
            ->where('provider_id', $providerId)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($reviews);
    }

    /**
     * Create a new review (requires contact record)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'provider_id' => 'required|exists:service_providers,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = auth()->user();

        // Ensure user is of type User (not Provider or Admin)
        if (!($user instanceof \App\Models\User)) {
            return response()->json(['error' => 'Only customers can leave reviews'], 403);
        }

        // Check if contact record exists
        $contactExists = ContactRecord::where('user_id', $user->id)
            ->where('provider_id', $request->provider_id)
            ->exists();

        if (!$contactExists) {
            return response()->json([
                'error' => 'You must contact the provider before leaving a review'
            ], 403);
        }

        // Check if user already reviewed this provider
        $existingReview = Review::where('user_id', $user->id)
            ->where('provider_id', $request->provider_id)
            ->first();

        if ($existingReview) {
            return response()->json(['error' => 'You have already reviewed this provider'], 400);
        }

        $review = Review::create([
            'user_id' => $user->id,
            'provider_id' => $request->provider_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        // Update provider's average rating
        $provider = ServiceProvider::find($request->provider_id);
        $provider->updateRating();

        return response()->json([
            'message' => 'Review submitted successfully',
            'review' => $review->load('user')
        ], 201);
    }
}
