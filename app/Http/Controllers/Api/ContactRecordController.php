<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContactRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContactRecordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Create a contact record (called when user contacts provider)
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'provider_id' => 'required|exists:service_providers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = auth()->user();

        // Ensure user is of type User (not Provider or Admin)
        if (!($user instanceof \App\Models\User)) {
            return response()->json(['error' => 'Only customers can create contact records'], 403);
        }

        // Create or retrieve existing contact record
        $contactRecord = ContactRecord::firstOrCreate([
            'user_id' => $user->id,
            'provider_id' => $request->provider_id,
        ]);

        return response()->json([
            'message' => 'Contact recorded successfully',
            'contact_record' => $contactRecord
        ], 201);
    }

    /**
     * Check if contact record exists
     */
    public function check(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'provider_id' => 'required|exists:service_providers,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = auth()->user();

        if (!($user instanceof \App\Models\User)) {
            return response()->json(['has_contacted' => false]);
        }

        $exists = ContactRecord::where('user_id', $user->id)
            ->where('provider_id', $request->provider_id)
            ->exists();

        return response()->json(['has_contacted' => $exists]);
    }
}
