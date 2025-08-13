<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MediaService;
use App\Services\UsernameService;
use App\Services\ProfileUrlService;
use App\Services\NotificationService;
use App\Rules\UsernameRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class ProfileController extends Controller
{
    protected $mediaService;
    protected $usernameService;
    protected $profileUrlService;
    protected $notificationService;

    public function __construct(
        MediaService $mediaService, 
        UsernameService $usernameService, 
        ProfileUrlService $profileUrlService,
        NotificationService $notificationService
    ) {
        $this->mediaService = $mediaService;
        $this->usernameService = $usernameService;
        $this->profileUrlService = $profileUrlService;
        $this->notificationService = $notificationService;
    }

    public function show($identifier = null)
    {
        // If no identifier is provided and user is not authenticated, redirect to login
        if (!$identifier && !Auth::check()) {
            return redirect()->route('login');
        }
        
        // If no identifier is provided and user is authenticated, redirect to their own profile
        if (!$identifier && Auth::check()) {
            $user = Auth::user();
            $profileIdentifier = $this->profileUrlService->getPreferredIdentifier($user);
            return redirect()->route('profile.show', ['identifier' => $profileIdentifier]);
        }
        
        // Check if this is an ID-based URL that should redirect to username
        if (is_numeric($identifier) && $this->profileUrlService->shouldRedirectToUsername($identifier)) {
            $usernameUrl = $this->profileUrlService->getUsernameUrl($identifier);
            if ($usernameUrl) {
                return redirect($usernameUrl, 301); // Permanent redirect for SEO
            }
        }
        
        // Find user by username or ID
        $user = $this->findUserByIdentifier($identifier);
        
        if (!$user) {
            abort(404, 'Profile not found.');
        }
        
        /** @var \App\Models\User|null $currentUser */
        $currentUser = Auth::user();
        
        // Check if profile is accessible
        $isOwnProfile = $currentUser && $currentUser->id === $user->id;
        $isProfilePublic = $user->is_public;
        $canViewProfile = $isOwnProfile || $isProfilePublic;
        
        // Create profile view notification if someone else is viewing the profile
        if ($currentUser && !$isOwnProfile && $canViewProfile) {
            $this->notificationService->createProfileViewNotification($user, $currentUser);
        }
        
        // Create a minimal user data array to avoid serialization issues
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'title' => $user->title,
            'date_of_birth' => $user->date_of_birth,
            'location' => $user->location,
            'bio' => $user->bio,
            'height_cm' => $user->height_cm,
            'weight_kg' => $user->weight_kg,
            'passion' => $user->passion,
            'profession' => $user->profession,
            'mission' => $user->mission,
            'calling' => $user->calling,
            'about_content' => $user->about_content,
            'connections_count' => $user->connections_count,
            'tributes_count' => $user->tributes_count,
            'flowers_count' => $user->flowers_count,
            'is_public' => $user->is_public,
            'last_activity' => $user->last_activity,
            'banner_path' => $user->banner_path,
            'profile_photo_url' => $user->getProfilePhotoUrl(),
            'banner_url' => $user->getBannerUrl(),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'education' => $user->education,
        ];
        
        // Debug logging to see what's being sent
        \Log::info('Profile data being sent to frontend', [
            'user_id' => $user->id,
            'profile_photo_url' => $userData['profile_photo_url'],
            'banner_url' => $userData['banner_url'],
            'user_data_keys' => array_keys($userData)
        ]);
        

        
        // Add settings only for own profile
        if ($isOwnProfile) {
            $userData['settings'] = $user->settings;
        }
        
        return Inertia::render('Profile/DynamicProfile', [
            'profileUser' => $userData,
            'isOwnProfile' => $isOwnProfile,
            'canViewProfile' => $canViewProfile,
            'isProfilePublic' => $isProfilePublic,
            'activeTab' => 'about', // Default tab
        ]);
    }

    public function update(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        \Log::info('Profile update request received', [
            'user_id' => $user->id,
            'request_data' => $request->all()
        ]);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                new UsernameRule,
                'unique:users,username,' . $user->id,
            ],
            'title' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'height_cm' => 'nullable|integer|min:50|max:300',
            'weight_kg' => 'nullable|integer|min:20|max:500',
            'passion' => 'nullable|string',
            'profession' => 'nullable|string',
            'mission' => 'nullable|string',
            'calling' => 'nullable|string',
            'about_content' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            \Log::error('Profile update validation failed', [
                'user_id' => $user->id,
                'errors' => $validator->errors()->toArray()
            ]);
            return back()->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();
        
        \Log::info('Profile update validated data', [
            'user_id' => $user->id,
            'validated_data' => $validated
        ]);

        // Handle photo upload if present
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo');
        }

        $user->update($validated);
        
        \Log::info('Profile update completed', [
            'user_id' => $user->id,
            'updated_fields' => array_keys($validated)
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function togglePrivacy(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->update([
            'is_public' => !$user->is_public
        ]);
        
        // Refresh the user data
        $user->refresh();
        
        return response()->json([
            'success' => true,
            'is_public' => $user->is_public,
            'message' => $user->is_public ? 'Profile is now public' : 'Profile is now private',
            'props' => [
                'isProfilePublic' => $user->is_public,
                'canViewProfile' => $user->is_public // For non-owners, this depends on is_public
            ]
        ]);
    }

    /**
     * Update user's banner
     */
    public function updateBanner(Request $request)
    {
        $request->validate([
            'banner' => 'required|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        try {
            $user = Auth::user();
            
            // Delete old banner if exists
            if ($user->banner_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->banner_path);
            }
            
            // Store new banner
            $bannerPath = $request->file('banner')->store('users/' . $user->id . '/banners', 'public');
            
            $user->update([
                'banner_path' => $bannerPath,
                'banner_url' => asset('storage/' . $bannerPath),
            ]);

            return back()->with('success', 'Banner updated successfully');
            
        } catch (\Exception $e) {
            return back()->withErrors(['banner' => 'Failed to update banner: ' . $e->getMessage()]);
        }
    }

    public function updatePicture(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:1048576', // 1GB max for scalability
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            // Use MediaService to handle the upload (it will clear existing media automatically)
            $media = $this->mediaService->uploadMedia(
                $user,
                $request->file('photo'),
                'profile-photos',
                [],
                [
                    'required',
                    'image',
                    'mimes:jpeg,png,jpg,gif,webp',
                    'max:1048576', // 1GB max
                ]
            );

            // Log the update for debugging
            \Log::info('Profile picture updated for user: ' . $user->id);

            return back()->with('success', 'Profile picture updated successfully.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update profile picture: ' . $e->getMessage());
        }
    }



    public function showVideo($identifier)
    {
        \Log::info('showVideo method called with identifier:', ['identifier' => $identifier]);
        
        // Check if this is an ID-based URL that should redirect to username
        if (is_numeric($identifier) && $this->profileUrlService->shouldRedirectToUsername($identifier)) {
            $user = User::find($identifier);
            if ($user && $user->username) {
                return redirect()->route('profile.video', ['identifier' => $user->username], 301);
            }
        }
        
        $user = $this->findUserByIdentifier($identifier);
        
        if (!$user) {
            abort(404, 'Profile not found.');
        }
        
        $currentUser = Auth::user();
        $isOwnProfile = $currentUser && $currentUser->id === $user->id;
        $isProfilePublic = $user->is_public;
        $canViewProfile = $isOwnProfile || $isProfilePublic;
        
        if (!$canViewProfile) {
            abort(403, 'This profile is private.');
        }
        
        \Log::info('Rendering video tab for user:', [
            'user_id' => $user->id,
            'activeTab' => 'video',
            'isOwnProfile' => $isOwnProfile
        ]);
        
        // Videos are now handled by the unified media system
        $videos = [];

        // Create the same user data structure as the main show method
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'title' => $user->title,
            'date_of_birth' => $user->date_of_birth,
            'location' => $user->location,
            'bio' => $user->bio,
            'height_cm' => $user->height_cm,
            'weight_kg' => $user->weight_kg,
            'passion' => $user->passion,
            'profession' => $user->profession,
            'mission' => $user->mission,
            'calling' => $user->calling,
            'about_content' => $user->about_content,
            'connections_count' => $user->connections_count,
            'tributes_count' => $user->tributes_count,
            'flowers_count' => $user->flowers_count,
            'is_public' => $user->is_public,
            'last_activity' => $user->last_activity,
            'banner_path' => $user->banner_path,
            'profile_photo_url' => $user->getProfilePhotoUrl(),
            'banner_url' => $user->getBannerUrl(),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'education' => $user->education,
        ];

        return Inertia::render('Profile/DynamicProfile', [
            'profileUser' => $userData,
            'isOwnProfile' => $isOwnProfile,
            'canViewProfile' => $canViewProfile,
            'isProfilePublic' => $isProfilePublic,
            'activeTab' => 'video',
            'videos' => $videos,
        ]);
    }

    public function showFamilyTree($identifier)
    {
        \Log::info('showFamilyTree method called with identifier:', ['identifier' => $identifier]);
        
        // Check if this is an ID-based URL that should redirect to username
        if (is_numeric($identifier) && $this->profileUrlService->shouldRedirectToUsername($identifier)) {
            $user = User::find($identifier);
            if ($user && $user->username) {
                return redirect()->route('profile.familytree', ['identifier' => $user->username], 301);
            }
        }
        
        $user = $this->findUserByIdentifier($identifier);
        
        if (!$user) {
            abort(404, 'Profile not found.');
        }
        
        $currentUser = Auth::user();
        $isOwnProfile = $currentUser && $currentUser->id === $user->id;
        $isProfilePublic = $user->is_public;
        $canViewProfile = $isOwnProfile || $isProfilePublic;
        
        if (!$canViewProfile) {
            abort(403, 'This profile is private.');
        }
        
        \Log::info('Rendering family tree tab for user:', [
            'user_id' => $user->id,
            'activeTab' => 'familytree',
            'isOwnProfile' => $isOwnProfile
        ]);

        // Create the same user data structure as the main show method
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'title' => $user->title,
            'date_of_birth' => $user->date_of_birth,
            'location' => $user->location,
            'bio' => $user->bio,
            'height_cm' => $user->height_cm,
            'weight_kg' => $user->weight_kg,
            'passion' => $user->passion,
            'profession' => $user->profession,
            'mission' => $user->mission,
            'calling' => $user->calling,
            'about_content' => $user->about_content,
            'connections_count' => $user->connections_count,
            'tributes_count' => $user->tributes_count,
            'flowers_count' => $user->flowers_count,
            'is_public' => $user->is_public,
            'last_activity' => $user->last_activity,
            'banner_path' => $user->banner_path,
            'profile_photo_url' => $user->getProfilePhotoUrl(),
            'banner_url' => $user->getBannerUrl(),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'education' => $user->education,
        ];

        return Inertia::render('Profile/DynamicProfile', [
            'profileUser' => $userData,
            'isOwnProfile' => $isOwnProfile,
            'canViewProfile' => $canViewProfile,
            'isProfilePublic' => $isProfilePublic,
            'activeTab' => 'familytree',
        ]);
    }

    public function showLetters($identifier)
    {
        // Check if this is an ID-based URL that should redirect to username
        if (is_numeric($identifier) && $this->profileUrlService->shouldRedirectToUsername($identifier)) {
            $user = User::find($identifier);
            if ($user && $user->username) {
                return redirect()->route('profile.letters', ['identifier' => $user->username], 301);
            }
        }
        
        $user = $this->findUserByIdentifier($identifier);
        
        if (!$user) {
            abort(404, 'Profile not found.');
        }
        
        $currentUser = Auth::user();
        $isOwnProfile = $currentUser && $currentUser->id === $user->id;
        $isProfilePublic = $user->is_public;
        $canViewProfile = $isOwnProfile || $isProfilePublic;
        
        if (!$canViewProfile) {
            abort(403, 'This profile is private.');
        }
        
        // Letters are now handled by the unified media system
        $letters = [];

        // Create the same user data structure as the main show method
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'title' => $user->title,
            'date_of_birth' => $user->date_of_birth,
            'location' => $user->location,
            'bio' => $user->bio,
            'height_cm' => $user->height_cm,
            'weight_kg' => $user->weight_kg,
            'passion' => $user->passion,
            'profession' => $user->profession,
            'mission' => $user->mission,
            'calling' => $user->calling,
            'about_content' => $user->about_content,
            'connections_count' => $user->connections_count,
            'tributes_count' => $user->tributes_count,
            'flowers_count' => $user->flowers_count,
            'is_public' => $user->is_public,
            'last_activity' => $user->last_activity,
            'banner_path' => $user->banner_path,
            'profile_photo_url' => $user->getProfilePhotoUrl(),
            'banner_url' => $user->getBannerUrl(),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'education' => $user->education,
        ];

        return Inertia::render('Profile/DynamicProfile', [
            'profileUser' => $userData,
            'isOwnProfile' => $isOwnProfile,
            'canViewProfile' => $canViewProfile,
            'isProfilePublic' => $isProfilePublic,
            'activeTab' => 'letters',
            'letters' => $letters,
        ]);
    }

    public function showPhotos($identifier)
    {
        // Check if this is an ID-based URL that should redirect to username
        if (is_numeric($identifier) && $this->profileUrlService->shouldRedirectToUsername($identifier)) {
            $user = User::find($identifier);
            if ($user && $user->username) {
                return redirect()->route('profile.photos', ['identifier' => $user->username], 301);
            }
        }
        
        $user = $this->findUserByIdentifier($identifier);
        
        if (!$user) {
            abort(404, 'Profile not found.');
        }
        
        $currentUser = Auth::user();
        $isOwnProfile = $currentUser && $currentUser->id === $user->id;
        $isProfilePublic = $user->is_public;
        $canViewProfile = $isOwnProfile || $isProfilePublic;
        
        if (!$canViewProfile) {
            abort(403, 'This profile is private.');
        }
        
        // Photos are now handled by the unified media system
        $photos = [];
        
        // Create the same user data structure as the main show method
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'title' => $user->title,
            'date_of_birth' => $user->date_of_birth,
            'location' => $user->location,
            'bio' => $user->bio,
            'height_cm' => $user->height_cm,
            'weight_kg' => $user->weight_kg,
            'passion' => $user->passion,
            'profession' => $user->profession,
            'mission' => $user->mission,
            'calling' => $user->calling,
            'about_content' => $user->about_content,
            'connections_count' => $user->connections_count,
            'tributes_count' => $user->tributes_count,
            'flowers_count' => $user->flowers_count,
            'is_public' => $user->is_public,
            'last_activity' => $user->last_activity,
            'banner_path' => $user->banner_path,
            'profile_photo_url' => $user->getProfilePhotoUrl(),
            'banner_url' => $user->getBannerUrl(),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'education' => $user->education,
        ];

        return Inertia::render('Profile/DynamicProfile', [
            'profileUser' => $userData,
            'isOwnProfile' => $isOwnProfile,
            'canViewProfile' => $canViewProfile,
            'isProfilePublic' => $isProfilePublic,
            'activeTab' => 'photos',
            'photos' => $photos,
        ]);
    }

    public function showNotifications($identifier)
    {
        // Check if this is an ID-based URL that should redirect to username
        if (is_numeric($identifier) && $this->profileUrlService->shouldRedirectToUsername($identifier)) {
            $user = User::find($identifier);
            if ($user && $user->username) {
                return redirect()->route('profile.notifications', ['identifier' => $user->username], 301);
            }
        }
        
        $user = $this->findUserByIdentifier($identifier);
        
        if (!$user) {
            abort(404, 'Profile not found.');
        }
        
        $currentUser = Auth::user();
        $isOwnProfile = $currentUser && $currentUser->id === $user->id;
        
        // Only allow users to view their own notifications
        if (!$isOwnProfile) {
            abort(403, 'You can only view your own notifications.');
        }
        
        // Create the same user data structure as the main show method
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'title' => $user->title,
            'date_of_birth' => $user->date_of_birth,
            'location' => $user->location,
            'bio' => $user->bio,
            'height_cm' => $user->height_cm,
            'weight_kg' => $user->weight_kg,
            'passion' => $user->passion,
            'profession' => $user->profession,
            'mission' => $user->mission,
            'calling' => $user->calling,
            'about_content' => $user->about_content,
            'connections_count' => $user->connections_count,
            'tributes_count' => $user->tributes_count,
            'flowers_count' => $user->flowers_count,
            'is_public' => $user->is_public,
            'last_activity' => $user->last_activity,
            'banner_path' => $user->banner_path,
            'profile_photo_url' => $user->getProfilePhotoUrl(),
            'banner_url' => $user->getBannerUrl(),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'education' => $user->education,
        ];

        return Inertia::render('Profile/DynamicProfile', [
            'profileUser' => $userData,
            'isOwnProfile' => $isOwnProfile,
            'canViewProfile' => true,
            'isProfilePublic' => $user->is_public,
            'activeTab' => 'notifications',
        ]);
    }

    /**
     * Find user by identifier (username or ID)
     */
    private function findUserByIdentifier($identifier)
    {
        if (is_numeric($identifier)) {
            return User::with('education')->find($identifier);
        } else {
            return User::with('education')->where('username', $identifier)->first();
        }
    }

    /**
     * Get the preferred identifier for a user (username if available, otherwise ID)
     */
    public function getPreferredIdentifier(User $user): string
    {
        return $user->username ?: (string) $user->id;
    }
} 