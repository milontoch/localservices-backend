<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProviderVerification;
use App\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProviderVerificationController extends Controller
{
    protected $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->middleware('auth:api');
        $this->cloudinaryService = $cloudinaryService;
    }

    /**
     * Upload verification document
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'provider_id' => 'required|exists:service_providers,id',
            'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB max
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
            $documentUrl = $this->cloudinaryService->uploadImage($request->file('document'), 'verifications');

            $verification = ProviderVerification::create([
                'provider_id' => $request->provider_id,
                'document_url' => $documentUrl,
                'status' => 'pending',
            ]);

            return response()->json([
                'message' => 'Verification document uploaded successfully. Awaiting admin approval.',
                'verification' => $verification
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
