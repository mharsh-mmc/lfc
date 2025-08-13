<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Search routes
Route::get('/search/profiles', [App\Http\Controllers\SearchController::class, 'searchProfiles']);
Route::get('/search/suggestions', [App\Http\Controllers\SearchController::class, 'getSuggestions']);

// Deceased profiles routes
Route::middleware('auth:web,sanctum')->group(function () {
    Route::get('/deceased-profiles/user', [App\Http\Controllers\DeceasedProfileController::class, 'getUserProfiles']);
    Route::get('/deceased-profiles/public', [App\Http\Controllers\DeceasedProfileController::class, 'getPublicProfiles']);
    Route::post('/deceased-profiles', [App\Http\Controllers\DeceasedProfileController::class, 'store']);
    Route::put('/deceased-profiles/{profile}', [App\Http\Controllers\DeceasedProfileController::class, 'update']);
    Route::delete('/deceased-profiles/{profile}', [App\Http\Controllers\DeceasedProfileController::class, 'destroy']);
    Route::patch('/deceased-profiles/{profile}/toggle-visibility', [App\Http\Controllers\DeceasedProfileController::class, 'toggleVisibility']);
    
    // Media routes - now using unified system via web routes
});

// Family Tree API routes
Route::prefix('profiles/{userId}/familytree')->group(function () {
    // Public routes (viewing)
    Route::get('/', [App\Http\Controllers\Api\FamilyTreeController::class, 'getTree']);
    
    // Protected routes (building/editing)
    Route::middleware('auth:web,sanctum')->group(function () {
        Route::post('/node', [App\Http\Controllers\Api\FamilyTreeController::class, 'addNode']);
        Route::patch('/node/{nodeId}', [App\Http\Controllers\Api\FamilyTreeController::class, 'updateNode']);
        Route::delete('/node/{nodeId}', [App\Http\Controllers\Api\FamilyTreeController::class, 'deleteNode']);
        
        Route::post('/edge', [App\Http\Controllers\Api\FamilyTreeController::class, 'addEdge']);
        Route::patch('/edge/{edgeId}', [App\Http\Controllers\Api\FamilyTreeController::class, 'updateEdge']);
        Route::delete('/edge/{edgeId}', [App\Http\Controllers\Api\FamilyTreeController::class, 'deleteEdge']);
        
        Route::post('/save', [App\Http\Controllers\Api\FamilyTreeController::class, 'saveFamilyTree']);
        Route::post('/generate-layouts', [App\Http\Controllers\Api\FamilyTreeController::class, 'generateLayouts']);
        
        // Layout management routes
        Route::post('/layout', [App\Http\Controllers\Api\FamilyTreeController::class, 'saveLayout']);
        Route::get('/layout/custom', [App\Http\Controllers\Api\FamilyTreeController::class, 'getCustomLayout']);
        Route::get('/layouts', [App\Http\Controllers\Api\FamilyTreeController::class, 'getLayouts']);
        Route::delete('/layout/{layoutId}', [App\Http\Controllers\Api\FamilyTreeController::class, 'deleteLayout']);
        
        Route::get('/search', [App\Http\Controllers\Api\FamilyTreeController::class, 'searchProfiles']);
        Route::post('/create-profile', [App\Http\Controllers\Api\FamilyTreeController::class, 'createProfileAndAdd']);
    });
});
