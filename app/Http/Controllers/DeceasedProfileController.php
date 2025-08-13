<?php

namespace App\Http\Controllers;

use App\Models\DeceasedProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class DeceasedProfileController extends Controller
{
    /**
     * Get user's deceased profiles
     */
    public function getUserProfiles(): JsonResponse
    {
        $profiles = DeceasedProfile::byCreator(Auth::id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($profile) {
                return [
                    'id' => $profile->id,
                    'name' => $profile->name,
                    'birth_date' => $profile->birth_date instanceof Carbon ? $profile->birth_date->format('Y-m-d') : null,
                    'death_date' => $profile->death_date instanceof Carbon ? $profile->death_date->format('Y-m-d') : null,
                    'birth_place' => $profile->birth_place,
                    'death_place' => $profile->death_place,
                    'biography' => $profile->biography,
                    'memorial_message' => $profile->memorial_message,
                    'relationship' => $profile->relationship,
                    'years_lived' => $profile->years_lived,
                    'age_at_death' => $profile->age_at_death,
                    'is_public' => $profile->is_public,
                    'profile_photo_url' => $profile->profile_photo_url,
                    'created_at' => $profile->created_at,
                ];
            });

        return response()->json([
            'data' => $profiles
        ]);
    }

    /**
     * Get public deceased profiles for home page
     */
    public function getPublicProfiles(): JsonResponse
    {
        $profiles = DeceasedProfile::public()
            ->with('creator:id,name')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get()
            ->map(function ($profile) {
                return [
                    'id' => $profile->id,
                    'name' => $profile->name,
                    'years_lived' => $profile->years_lived,
                    'memorial_message' => $profile->memorial_message ?: 'A lifetime of love and memories',
                    'creator_name' => $profile->creator->name ?? 'Anonymous',
                    'profile_photo_url' => $profile->profile_photo_url,
                ];
            });

        return response()->json([
            'data' => $profiles
        ]);
    }

    /**
     * Store a new deceased profile
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'birth_date' => 'required|date|before_or_equal:today|after:1900-01-01',
            'death_date' => 'required|date|after:birth_date|before_or_equal:today',
            'birth_place' => 'nullable|string|max:255',
            'death_place' => 'nullable|string|max:255',
            'biography' => 'nullable|string|max:5000',
            'memorial_message' => 'nullable|string|max:1000',
            'relationship' => 'nullable|string|max:255',
            'is_public' => 'boolean',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $profileData = [
                'created_by' => Auth::id(),
                'name' => $request->name,
                'birth_date' => $request->birth_date,
                'death_date' => $request->death_date,
                'birth_place' => $request->birth_place,
                'death_place' => $request->death_place,
                'biography' => $request->biography,
                'memorial_message' => $request->memorial_message,
                'relationship' => $request->relationship,
                'is_public' => $request->is_public ?? false,
            ];

            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                $photoPath = $request->file('profile_photo')->store('deceased-profiles/photos', 'public');
                $profileData['profile_photo_path'] = $photoPath;
            }

            $profile = DeceasedProfile::create($profileData);

            return response()->json([
                'success' => true,
                'message' => 'Memorial profile created successfully',
                'data' => [
                    'id' => $profile->id,
                    'name' => $profile->name,
                    'birth_date' => $profile->birth_date instanceof Carbon ? $profile->birth_date->format('Y-m-d') : null,
                    'death_date' => $profile->death_date instanceof Carbon ? $profile->death_date->format('Y-m-d') : null,
                    'birth_place' => $profile->birth_place,
                    'death_place' => $profile->death_place,
                    'biography' => $profile->biography,
                    'memorial_message' => $profile->memorial_message,
                    'relationship' => $profile->relationship,
                    'years_lived' => $profile->years_lived,
                    'age_at_death' => $profile->age_at_death,
                    'is_public' => $profile->is_public,
                    'profile_photo_url' => $profile->profile_photo_url,
                    'created_at' => $profile->created_at,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create memorial profile: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a deceased profile
     */
    public function update(Request $request, DeceasedProfile $profile): JsonResponse
    {
        // Check if user owns this profile
        if ($profile->created_by !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'birth_date' => 'sometimes|required|date|before_or_equal:today|after:1900-01-01',
            'death_date' => 'sometimes|required|date|after:birth_date|before_or_equal:today',
            'birth_place' => 'nullable|string|max:255',
            'death_place' => 'nullable|string|max:255',
            'biography' => 'nullable|string|max:5000',
            'memorial_message' => 'nullable|string|max:1000',
            'relationship' => 'nullable|string|max:255',
            'is_public' => 'boolean',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = $request->only([
                'name', 'birth_date', 'death_date', 'birth_place', 
                'death_place', 'biography', 'memorial_message', 'relationship', 'is_public'
            ]);

            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                // Delete old photo if exists
                if ($profile->profile_photo_path) {
                    Storage::disk('public')->delete($profile->profile_photo_path);
                }
                
                $photoPath = $request->file('profile_photo')->store('deceased-profiles/photos', 'public');
                $updateData['profile_photo_path'] = $photoPath;
            }

            $profile->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Memorial profile updated successfully',
                'data' => [
                    'id' => $profile->id,
                    'name' => $profile->name,
                    'birth_date' => $profile->birth_date instanceof Carbon ? $profile->birth_date->format('Y-m-d') : null,
                    'death_date' => $profile->death_date instanceof Carbon ? $profile->death_date->format('Y-m-d') : null,
                    'birth_place' => $profile->birth_place,
                    'death_place' => $profile->death_place,
                    'biography' => $profile->biography,
                    'memorial_message' => $profile->memorial_message,
                    'relationship' => $profile->relationship,
                    'years_lived' => $profile->years_lived,
                    'age_at_death' => $profile->age_at_death,
                    'is_public' => $profile->is_public,
                    'profile_photo_url' => $profile->profile_photo_url,
                    'created_at' => $profile->created_at,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update memorial profile: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a deceased profile
     */
    public function destroy(DeceasedProfile $profile): JsonResponse
    {
        // Check if user owns this profile
        if ($profile->created_by !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $profile->delete();

            return response()->json([
                'success' => true,
                'message' => 'Memorial profile deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete memorial profile: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle profile visibility
     */
    public function toggleVisibility(DeceasedProfile $profile): JsonResponse
    {
        // Check if user owns this profile
        if ($profile->created_by !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        try {
            $profile->is_public = !$profile->is_public;
            $profile->save();

            return response()->json([
                'success' => true,
                'is_public' => $profile->is_public,
                'message' => $profile->is_public ? 'Profile is now public' : 'Profile is now private'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle visibility: ' . $e->getMessage()
            ], 500);
        }
    }
}
