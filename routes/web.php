<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\User;

// Include auth routes
require __DIR__.'/auth.php';

// Include settings routes
require __DIR__.'/settings.php';

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/leave-a-mark', function () {
    return Inertia::render('LeaveAMark', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('leave-a-mark');

Route::get('/contact', function () {
    return Inertia::render('Contact', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('contact');



Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    // Settings routes
    Route::get('/settings', function () {
        return Inertia::render('Settings');
    })->name('settings');
    Route::post('/settings', [App\Http\Controllers\SettingsController::class, 'update'])->name('settings.update');
    
    // Profile routes with fallback URL schema
    Route::prefix('profile')->group(function () {
        // Main profile route with fallback support
        Route::get('/{identifier?}', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
        
        // Profile tab routes with fallback support
        Route::get('/{identifier}/familytree', [App\Http\Controllers\ProfileController::class, 'showFamilyTree'])->name('profile.familytree');
        Route::get('/{identifier}/video', [App\Http\Controllers\ProfileController::class, 'showVideo'])->name('profile.video');
        Route::get('/{identifier}/letters', [App\Http\Controllers\ProfileController::class, 'showLetters'])->name('profile.letters');
        Route::get('/{identifier}/photos', [App\Http\Controllers\ProfileController::class, 'showPhotos'])->name('profile.photos');
        Route::get('/{identifier}/notifications', [App\Http\Controllers\ProfileController::class, 'showNotifications'])->name('profile.notifications');
    });
    
    // Profile update routes (no identifier needed)
    Route::post('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/toggle-privacy', [App\Http\Controllers\ProfileController::class, 'togglePrivacy'])->name('profile.toggle-privacy');
    Route::post('/profile/banner', [App\Http\Controllers\ProfileController::class, 'updateBanner'])->name('profile.banner');
    Route::post('/profile/picture', [App\Http\Controllers\ProfileController::class, 'updatePicture'])->name('profile.picture');
    
    // Family Tree routes
    Route::prefix('family-tree')->group(function () {
        Route::post('/create-profile', [App\Http\Controllers\Api\FamilyTreeController::class, 'createProfileAndAdd'])->name('family-tree.create-profile');
        Route::get('/test', function() { return response()->json(['message' => 'Family tree route working']); })->name('family-tree.test');
        Route::get('/test-form', function() { 
            return '<form method="POST" action="/family-tree/create-profile">
                <input type="hidden" name="_token" value="' . csrf_token() . '">
                <input type="text" name="name" placeholder="Name" required><br>
                <input type="text" name="username" placeholder="Username" required><br>
                <input type="text" name="relation" value="parent" required><br>
                <input type="number" name="x_position" value="0" required><br>
                <input type="number" name="y_position" value="0" required><br>
                <button type="submit">Create Profile</button>
            </form>';
        })->name('family-tree.test-form');
    });
    
    // Education routes
    Route::post('/education', [App\Http\Controllers\EducationController::class, 'store'])->name('education.store');
    Route::put('/education/{education}', [App\Http\Controllers\EducationController::class, 'update'])->name('education.update');
    Route::delete('/education/{education}', [App\Http\Controllers\EducationController::class, 'destroy'])->name('education.destroy');
    Route::post('/education/reorder', [App\Http\Controllers\EducationController::class, 'reorder'])->name('education.reorder');
    

    
    // Unified Media routes
    Route::middleware(['auth:sanctum', 'verified'])->prefix('media')->group(function () {
        Route::post('/upload', [App\Http\Controllers\MediaController::class, 'upload'])->name('media.upload');
        Route::get('/stats/{userId?}', [App\Http\Controllers\MediaController::class, 'getStats'])->name('media.stats');
        Route::get('/{userId?}', [App\Http\Controllers\MediaController::class, 'getMedia'])->name('media.get');
        Route::get('/{userId}/{type}', [App\Http\Controllers\MediaController::class, 'getMediaByType'])->name('media.getByType');
        Route::put('/{media}', [App\Http\Controllers\MediaController::class, 'update'])->name('media.update');
        Route::delete('/{media}', [App\Http\Controllers\MediaController::class, 'destroy'])->name('media.destroy');
    });
    
    // Legacy routes removed - now using unified media system
    
    // Notification routes
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/recent', [App\Http\Controllers\NotificationController::class, 'getRecent'])->name('notifications.recent');
    Route::get('/notifications/unread-count', [App\Http\Controllers\NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{id}/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
    
    // Test notification route
    Route::get('/test-notifications', function () {
        $user = Auth::user();
        $notificationService = app(App\Services\NotificationService::class);
        
        // Create some test notifications
        $notificationService->createProfileViewNotification($user, User::find(1));
        $notificationService->createSubscriptionReminderNotification($user, 3);
        $notificationService->createTributeAddedNotification($user, User::find(1), 'candle');
        
        return redirect()->back()->with('success', 'Test notifications created!');
    })->name('test-notifications');
});

// Migration Routes
Route::prefix('migration')->group(function () {
    Route::get('/', [App\Http\Controllers\MigrationController::class, 'index'])->name('migration.dashboard');
    Route::post('/import-old-database', [App\Http\Controllers\MigrationController::class, 'importOldDatabase'])->name('migration.import-old');
    Route::post('/migrate-core-data', [App\Http\Controllers\MigrationController::class, 'migrateCoreData'])->name('migration.migrate-core');
    Route::post('/migrate-tree/{treeId}', [App\Http\Controllers\MigrationController::class, 'migrateTree'])->name('migration.migrate-tree');
    Route::post('/migrate-all-trees', [App\Http\Controllers\MigrationController::class, 'migrateAllTrees'])->name('migration.migrate-all');
    Route::get('/status', [App\Http\Controllers\MigrationController::class, 'getStatus'])->name('migration.status');
    Route::get('/available-trees', [App\Http\Controllers\MigrationController::class, 'getAvailableTrees'])->name('migration.available-trees');
});


// Profile banner route
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/profile/banner', [App\Http\Controllers\ProfileController::class, 'updateBanner'])->name('profile.banner');
});

// Deceased Profiles API routes
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('/api/deceased-profiles/user', [App\Http\Controllers\DeceasedProfileController::class, 'getUserProfiles']);
    Route::post('/api/deceased-profiles', [App\Http\Controllers\DeceasedProfileController::class, 'store']);
    Route::put('/api/deceased-profiles/{profile}', [App\Http\Controllers\DeceasedProfileController::class, 'update']);
    Route::delete('/api/deceased-profiles/{profile}', [App\Http\Controllers\DeceasedProfileController::class, 'destroy']);
    Route::patch('/api/deceased-profiles/{profile}/toggle-visibility', [App\Http\Controllers\DeceasedProfileController::class, 'toggleVisibility']);
});

// Public API for home page
Route::get('/api/deceased-profiles/public', [App\Http\Controllers\DeceasedProfileController::class, 'getPublicProfiles']);

// Test route removed - unified media system is now integrated






