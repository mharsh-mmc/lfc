<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class MediaApiController extends Controller
{
    protected MediaService $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * Get the correct thumbnail URL for media
     */
    private function getThumbnailUrl(SpatieMedia $media, User $user): string
    {
        // For videos, use the custom thumbnail method
        if ($media->collection_name === 'videos') {
            return $user->getVideoThumbnailUrl($media);
        }
        
        // For other media types, use Media Library conversion
        return $media->getUrl('thumbnail');
    }

    /**
     * Upload media to any collection
     */
    public function uploadMedia(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:1048576', // 1GB max
            'collection' => 'required|string|in:videos,images,documents,profile-photos,banners',
            'caption' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $collection = $request->input('collection');
            $customProperties = [
                'caption' => $request->input('caption', ''),
            ];

            // Add collection-specific properties
            switch ($collection) {
                case 'videos':
                    $customProperties['duration'] = 0;
                    break;
                case 'documents':
                    $customProperties['page_count'] = 0;
                    break;
            }

            $media = $this->mediaService->uploadMedia(
                $user,
                $request->file('file'),
                $collection,
                $customProperties,
                [
                    'required',
                    'file',
                    'max:1048576', // 1GB max
                ]
            );

            return response()->json([
                'success' => true,
                'message' => ucfirst($collection) . ' uploaded successfully',
                'media' => [
                    'id' => $media->id,
                    'caption' => $media->getCustomProperty('caption', ''),
                    'url' => $media->getUrl(),
                    'thumbnail' => $this->getThumbnailUrl($media, $user),
                    'file_size' => $media->size,
                    'mime_type' => $media->mime_type,
                    'created_at' => $media->created_at,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload media: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's media with pagination
     */
    public function getUserMedia(Request $request, $userId, $collection): JsonResponse
    {
        $user = User::findOrFail($userId);
        $perPage = $request->input('per_page', 20);
        $page = $request->input('page', 1);

        try {
            $result = $this->mediaService->getUserMedia($user, $collection, $perPage);

            return response()->json([
                'success' => true,
                'data' => $result['data'],
                'pagination' => $result['pagination']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get media: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update media custom properties
     */
    public function updateMedia(Request $request, $mediaId): JsonResponse
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'collection' => 'required|string|in:videos,images,documents,profile-photos,banners',
            'caption' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $collection = $request->input('collection');
            $customProperties = [];

            if ($request->has('caption')) {
                $customProperties['caption'] = $request->input('caption');
            }

            $media = $this->mediaService->updateMedia($user, $mediaId, $collection, $customProperties);

            return response()->json([
                'success' => true,
                'message' => 'Media updated successfully',
                'media' => [
                    'id' => $media->id,
                    'caption' => $media->getCustomProperty('caption', ''),
                    'url' => $media->getUrl(),
                    'thumbnail' => $this->getThumbnailUrl($media, $user),
                    'file_size' => $media->size,
                    'mime_type' => $media->mime_type,
                    'created_at' => $media->created_at,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update media: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete media
     */
    public function deleteMedia(Request $request, $mediaId): JsonResponse
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'collection' => 'required|string|in:videos,images,documents,profile-photos,banners',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $collection = $request->input('collection');
            $this->mediaService->deleteMedia($user, $mediaId, $collection);

            return response()->json([
                'success' => true,
                'message' => 'Media deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete media: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get media statistics for user
     */
    public function getMediaStats($userId): JsonResponse
    {
        $user = User::findOrFail($userId);

        try {
            $stats = [
                'videos' => [
                    'count' => $user->getMedia('videos')->count(),
                    'limit' => 20,
                    'total_size' => $user->getMedia('videos')->sum('size'),
                ],
                'images' => [
                    'count' => $user->getMedia('images')->count(),
                    'limit' => 50,
                    'total_size' => $user->getMedia('images')->sum('size'),
                ],
                'documents' => [
                    'count' => $user->getMedia('documents')->count(),
                    'limit' => 20,
                    'total_size' => $user->getMedia('documents')->sum('size'),
                ],
                'profile_photos' => [
                    'count' => $user->getMedia('profile-photos')->count(),
                    'limit' => 1,
                ],
                'banners' => [
                    'count' => $user->getMedia('banners')->count(),
                    'limit' => 1,
                ],
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get media stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete media
     */
    public function bulkDeleteMedia(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'media_ids' => 'required|array',
            'media_ids.*' => 'integer',
            'collection' => 'required|string|in:videos,images,documents,profile-photos,banners',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $collection = $request->input('collection');
            $mediaIds = $request->input('media_ids');
            $deletedCount = 0;
            $errors = [];

            foreach ($mediaIds as $mediaId) {
                try {
                    $this->mediaService->deleteMedia($user, $mediaId, $collection);
                    $deletedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to delete media ID {$mediaId}: " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} items",
                'deleted_count' => $deletedCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk delete media: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's videos
     */
    public function getUserVideos(Request $request, $userId)
    {
        \Log::info('getUserVideos called', ['user_id' => $userId]);
        
        $user = User::findOrFail($userId);
        \Log::info('User found', ['user_id' => $user->id, 'user_name' => $user->name]);
        
        $videos = $user->getMedia('videos')->map(function ($media) use ($user) {
            \Log::info('Processing video media', ['media_id' => $media->id, 'file_name' => $media->file_name]);
            
            return [
                'id' => $media->id,
                'caption' => $media->getCustomProperty('caption', ''),
                'url' => $media->getUrl(),
                'thumbnail' => $this->getThumbnailUrl($media, $user),
                'file_size' => $media->size,
                'mime_type' => $media->mime_type,
                'created_at' => $media->created_at,
            ];
        });

        \Log::info('Videos processed', [
            'user_id' => $userId,
            'videos_count' => $videos->count(),
            'videos' => $videos->toArray()
        ]);

        // Always return JSON for API endpoints
        return response()->json([
            'success' => true,
            'videos' => $videos
        ]);
    }

    /**
     * Get user's images
     */
    public function getUserImages(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        $images = $user->getMedia('images')->map(function ($media) use ($user) {
            return [
                'id' => $media->id,
                'caption' => $media->getCustomProperty('caption', ''),
                'url' => $media->getUrl(),
                'thumbnail' => $this->getThumbnailUrl($media, $user),
                'file_size' => $media->size,
                'mime_type' => $media->mime_type,
                'created_at' => $media->created_at,
            ];
        });

        // Always return JSON for API endpoints
        return response()->json([
            'success' => true,
            'images' => $images
        ]);
    }

    /**
     * Get user's documents
     */
    public function getUserDocuments(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        $documents = $user->getMedia('documents')->map(function ($media) use ($user) {
            return [
                'id' => $media->id,
                'caption' => $media->getCustomProperty('caption', ''),
                'url' => $media->getUrl(),
                'thumbnail' => $this->getThumbnailUrl($media, $user),
                'file_size' => $media->size,
                'mime_type' => $media->mime_type,
                'created_at' => $media->created_at,
            ];
        });

        // Always return JSON for API endpoints
        return response()->json([
            'success' => true,
            'documents' => $documents
        ]);
    }
} 