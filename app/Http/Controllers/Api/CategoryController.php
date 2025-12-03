<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Get all categories
     */
    public function index()
    {
        $categories = Category::withCount('providers')->get();
        return response()->json($categories);
    }

    /**
     * Get a single category with its providers
     */
    public function show($slug)
    {
        $category = Category::where('slug', $slug)
            ->with(['providers' => function ($query) {
                $query->where('is_verified', true)
                    ->orderBy('rating_avg', 'desc')
                    ->limit(10);
            }])
            ->firstOrFail();

        return response()->json($category);
    }
}
