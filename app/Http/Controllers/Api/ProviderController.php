<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProviderController extends Controller
{
    /**
     * List and search providers with filters
     */
    public function index(Request $request)
    {
        $query = ServiceProvider::with(['category', 'reviews']);

        // Filter by category
        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filter by minimum rating
        if ($request->has('min_rating')) {
            $query->where('rating_avg', '>=', $request->min_rating);
        }

        // Filter by verification status (show only verified by default)
        $query->where('is_verified', true);

        // Distance-based search using Haversine formula
        if ($request->has('lat') && $request->has('lng')) {
            $lat = $request->lat;
            $lng = $request->lng;
            $radius = $request->radius_km ?? 50; // Default 50km radius

            // Haversine formula to calculate distance
            $query->select('service_providers.*')
                ->selectRaw(
                    '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                    [$lat, $lng, $lat]
                )
                ->having('distance', '<=', $radius)
                ->orderBy('distance');
        } else {
            // Default ordering by rating if no location provided
            $query->orderBy('rating_avg', 'desc');
        }

        $providers = $query->paginate(20);

        return response()->json($providers);
    }

    /**
     * Get a single provider by ID
     */
    public function show($id)
    {
        $provider = ServiceProvider::with([
            'category',
            'portfolios',
            'reviews.user',
            'verifications'
        ])->findOrFail($id);

        // Hide verification documents from public view
        $provider->verifications->makeHidden(['document_url', 'notes']);

        return response()->json($provider);
    }

    /**
     * Search providers by name or category
     */
    public function search(Request $request)
    {
        $query = ServiceProvider::with(['category'])
            ->where('is_verified', true);

        if ($request->has('q')) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('full_name', 'like', "%{$searchTerm}%")
                    ->orWhereHas('category', function ($q) use ($searchTerm) {
                        $q->where('name', 'like', "%{$searchTerm}%");
                    });
            });
        }

        $providers = $query->orderBy('rating_avg', 'desc')->paginate(20);

        return response()->json($providers);
    }
}
