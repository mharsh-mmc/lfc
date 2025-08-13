<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * Search for user profiles based on name and city
     */
    public function searchProfiles(Request $request)
    {
        $name = $request->input('name');
        $city = $request->input('city');

        // Start with a base query for public profiles
        $query = User::where('is_public', true);

        // Add name search if provided
        if (!empty($name)) {
            $query->where(function($q) use ($name) {
                $q->where('name', 'LIKE', "%{$name}%")
                  ->orWhere('first_name', 'LIKE', "%{$name}%")
                  ->orWhere('last_name', 'LIKE', "%{$name}%")
                  ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%{$name}%");
            });
        }

        // Add city search if provided
        if (!empty($city)) {
            $query->where(function($q) use ($city) {
                $q->where('city', 'LIKE', "%{$city}%")
                  ->orWhere('state', 'LIKE', "%{$city}%")
                  ->orWhere('country', 'LIKE', "%{$city}%");
            });
        }

        // Get results with pagination
        $profiles = $query->select([
            'id',
            'name',
            'first_name',
            'last_name',
            'city',
            'state',
            'country',
            'profile_photo_url',
            'bio',
            'profession',
            'is_public'
        ])
        ->orderBy('name')
        ->paginate(12);

        return response()->json($profiles);
    }

    /**
     * Get search suggestions for autocomplete
     */
    public function getSuggestions(Request $request)
    {
        $name = $request->input('name', '');
        $city = $request->input('city', '');

        // If both fields are empty or too short, return empty
        if ((empty($name) || strlen($name) < 2) && (empty($city) || strlen($city) < 2)) {
            return response()->json([]);
        }

        $query = User::where('is_public', true);

        // Build OR query for name and city
        $query->where(function($q) use ($name, $city) {
            // Name search
            if (!empty($name) && strlen($name) >= 2) {
                $q->where(function($nameQuery) use ($name) {
                    $nameQuery->where('name', 'LIKE', "%{$name}%")
                              ->orWhere('first_name', 'LIKE', "%{$name}%")
                              ->orWhere('last_name', 'LIKE', "%{$name}%")
                              ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%{$name}%");
                });
            }

            // City search
            if (!empty($city) && strlen($city) >= 2) {
                $q->orWhere(function($cityQuery) use ($city) {
                    $cityQuery->where('city', 'LIKE', "%{$city}%")
                              ->orWhere('state', 'LIKE', "%{$city}%")
                              ->orWhere('country', 'LIKE', "%{$city}%");
                });
            }
        });

        // Get suggestions with profile info
        $suggestions = $query->select([
            'id',
            'name',
            'first_name',
            'last_name',
            'city',
            'state',
            'country',
            'profession'
        ])
        ->limit(8)
        ->get()
        ->map(function($user) {
            $displayName = $user->name ?: trim($user->first_name . ' ' . $user->last_name);
            $location = trim($user->city . ', ' . $user->state . ', ' . $user->country);
            
            return [
                'id' => $user->id,
                'name' => $displayName,
                'location' => $location,
                'profession' => $user->profession,
                'profile_photo_url' => $user->getProfilePhotoUrl(),
                'display_text' => $displayName . ' • ' . $location
            ];
        });

        return response()->json($suggestions);
    }
}
