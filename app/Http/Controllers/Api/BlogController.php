<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    protected $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
        $this->middleware('auth:api')->except(['index', 'show']);
        $this->middleware('admin')->except(['index', 'show']);
    }

    /**
     * Get all published blog posts
     */
    public function index()
    {
        $posts = BlogPost::where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($posts);
    }

    /**
     * Get a single blog post by slug
     */
    public function show($slug)
    {
        $post = BlogPost::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return response()->json($post);
    }

    /**
     * Get all blog posts (including drafts) - Admin only
     */
    public function adminIndex()
    {
        $posts = BlogPost::orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($posts);
    }

    /**
     * Create a new blog post - Admin only
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'required|string',
            'author_name' => 'required|string',
            'status' => 'required|in:draft,published',
            'photo' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $slug = Str::slug($request->title);
        $originalSlug = $slug;
        $counter = 1;

        // Ensure unique slug
        while (BlogPost::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $photoUrl = null;
        if ($request->hasFile('photo')) {
            try {
                $photoUrl = $this->cloudinaryService->uploadImage($request->file('photo'), 'blog');
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 400);
            }
        }

        $post = BlogPost::create([
            'title' => $request->input('title'),
            'content' => $request->input('content'),
            'category' => $request->input('category'),
            'photo_url' => $photoUrl,
            'author_name' => $request->input('author_name'),
            'status' => $request->input('status'),
            'slug' => $slug,
        ]);

        return response()->json([
            'message' => 'Blog post created successfully',
            'post' => $post
        ], 201);
    }

    /**
     * Update a blog post - Admin only
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'category' => 'sometimes|string',
            'author_name' => 'sometimes|string',
            'status' => 'sometimes|in:draft,published',
            'photo' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $post = BlogPost::findOrFail($id);

        if ($request->has('title') && $request->title !== $post->title) {
            $slug = Str::slug($request->title);
            $originalSlug = $slug;
            $counter = 1;

            while (BlogPost::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $post->slug = $slug;
            $post->title = $request->title;
        }

        if ($request->hasFile('photo')) {
            try {
                $photoUrl = $this->cloudinaryService->uploadImage($request->file('photo'), 'blog');
                $post->photo_url = $photoUrl;
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 400);
            }
        }

        $post->fill($request->except(['title', 'photo']));
        $post->save();

        return response()->json([
            'message' => 'Blog post updated successfully',
            'post' => $post
        ]);
    }

    /**
     * Delete a blog post - Admin only
     */
    public function destroy($id)
    {
        $post = BlogPost::findOrFail($id);
        $post->delete();

        return response()->json(['message' => 'Blog post deleted successfully']);
    }
}
