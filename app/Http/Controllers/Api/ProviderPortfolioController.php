<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProviderPortfolio;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProviderPortfolioController extends Controller
{
    protected $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->middleware('auth:api');
        $this->cloudinaryService = $cloudinaryService;
    }

    /**
     * Upload portfolio image
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'provider_id' => 'required|exists:service_providers,id',
            'image' => 'required|file|mimes:jpg,jpeg,png|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verify that the authenticated user is the provider
        $user = auth()->user();
        if (!($user instanceof \App\Models\ServiceProvider) || $user->id != $request->provider_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $imageUrl = $this->cloudinaryService->uploadImage($request->file('image'), 'portfolios');

            $portfolio = ProviderPortfolio::create([
                'provider_id' => $request->provider_id,
                'image_url' => $imageUrl,
            ]);

            return response()->json([
                'message' => 'Portfolio image uploaded successfully',
                'portfolio' => $portfolio
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Delete portfolio image
     */
    public function destroy($id)
    {
        $portfolio = ProviderPortfolio::findOrFail($id);

        // Verify that the authenticated user is the provider
        $user = auth()->user();
        if (!($user instanceof \App\Models\ServiceProvider) || $user->id != $portfolio->provider_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $portfolio->delete();

        return response()->json(['message' => 'Portfolio image deleted successfully']);
    }
}
