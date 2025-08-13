<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class MediaController extends Controller
{
    protected $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * Upload media file using Spatie Media Library
     */
    public function upload(Request $request)
    {
        try {
            Log::info('Media upload request received', [
                'user_id' => Auth::id(),
                'request_data' => $request->except(['file']),
                'has_file' => $request->hasFile('file'),
                'file_size' => $request->file('file')?->getSize(),
                'file_mime' => $request->file('file')?->getMimeType(),
                'file_name' => $request->file('file')?->getClientOriginalName(),
                'all_inputs' => $request->all(),
                'files' => $request->allFiles()
            ]);

            // Validate request
            $validator = Validator::make($request->all(), [
                'media_type' => 'required|in:video,image,letter,document',
                'file' => 'required|file',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'is_private' => 'boolean'
            ]);

            if ($validator->fails()) {
                Log::warning('Media upload validation failed', [
                    'user_id' => Auth::id(),
                    'errors' => $validator->errors()->toArray()
                ]);
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get authenticated user
            $user = Auth::user();
            if (!$user) {
                Log::error('Media upload unauthorized - no user found');
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 401);
            }

            // Check user limits using Spatie Media Library collections
            $mediaType = $request->input('media_type');
            $collectionMap = [
                'video' => 'videos',
                'image' => 'images',
                'letter' => 'letters',
                'document' => 'documents'
            ];
            
            $collectionName = $collectionMap[$mediaType] ?? $mediaType;
            
            $limits = [
                'videos' => 20,
                'images' => 50,
                'letters' => 50,
                'documents' => 20
            ];
            
            if (!$user->canAddToCollection($collectionName)) {
                $limit = $limits[$collectionName] ?? 50;
                
                Log::warning('Media upload limit reached', [
                    'user_id' => $user->id,
                    'media_type' => $mediaType,
                    'collection' => $collectionName,
                    'limit' => $limit
                ]);
                
                return response()->json([
                    'success' => false,
                    'error' => "You've reached the maximum limit of {$limit} {$mediaType}s"
                ], 400);
            }

            // Validate file type and size
            $file = $request->file('file');
            
            if (!$file) {
                Log::error('No file received in upload request', [
                    'user_id' => $user->id,
                    'media_type' => $mediaType,
                    'request_files' => $request->allFiles()
                ]);
                return response()->json([
                    'success' => false,
                    'error' => 'No file received'
                ], 400);
            }
            
            // Store file information once to avoid multiple calls to getSize()
            try {
                $fileSize = $file->getSize();
                $fileName = $file->getClientOriginalName();
                $mimeType = $file->getMimeType();
                
                // Verify file is still valid
                if ($fileSize === false || $fileSize === null) {
                    throw new \Exception('Unable to determine file size');
                }
                
                if (!$fileName) {
                    throw new \Exception('Unable to determine file name');
                }
                
                if (!$mimeType) {
                    throw new \Exception('Unable to determine MIME type');
                }
            } catch (\Exception $e) {
                Log::error('File validation failed', [
                    'user_id' => $user->id,
                    'media_type' => $mediaType,
                    'error' => $e->getMessage(),
                    'file_valid' => $file->isValid()
                ]);
                return response()->json([
                    'success' => false,
                    'error' => 'File validation failed: ' . $e->getMessage()
                ], 400);
            }
            
            $maxSizes = [
                'video' => 100 * 1024 * 1024, // 100MB
                'image' => 10 * 1024 * 1024,  // 10MB
                'letter' => 10 * 1024 * 1024, // 10MB
                'document' => 50 * 1024 * 1024 // 50MB
            ];

            Log::info('File validation', [
                'user_id' => $user->id,
                'media_type' => $mediaType,
                'file_size' => $fileSize,
                'max_size' => $maxSizes[$mediaType] ?? 10 * 1024 * 1024,
                'file_name' => $fileName,
                'mime_type' => $mimeType
            ]);

            if ($fileSize > ($maxSizes[$mediaType] ?? 10 * 1024 * 1024)) {
                Log::warning('Media upload file size exceeded', [
                    'user_id' => $user->id,
                    'media_type' => $mediaType,
                    'file_size' => $fileSize,
                    'max_size' => $maxSizes[$mediaType]
                ]);
                return response()->json([
                    'success' => false,
                    'error' => "File size exceeds the maximum limit for {$mediaType}s"
                ], 400);
            }

            Log::info('Adding media to user using Spatie Media Library', [
                'user_id' => $user->id,
                'media_type' => $mediaType,
                'title' => $request->input('title')
            ]);

            // Add media file using Spatie Media Library directly to User model
            $collectionMap = [
                'video' => 'videos',
                'image' => 'images',
                'letter' => 'letters',
                'document' => 'documents'
            ];
            
            $collectionName = $collectionMap[$mediaType] ?? $mediaType;
            
            // Verify file is still valid before adding to media collection
            if (!$file->isValid()) {
                Log::error('File became invalid before media collection processing', [
                    'user_id' => $user->id,
                    'media_type' => $mediaType,
                    'file_name' => $fileName
                ]);
                
                return response()->json([
                    'success' => false,
                    'error' => 'File became invalid during processing. Please try uploading again.'
                ], 400);
            }
            
            $spatieMedia = null;
            
            try {
                // For videos, use MediaService to handle thumbnail generation
                if ($mediaType === 'video') {
                    $spatieMedia = $this->mediaService->uploadMedia(
                        $user,
                        $file,
                        $collectionName,
                        [
                            'title' => $request->input('title'),
                            'media_type' => $mediaType,
                            'description' => $request->input('description'),
                            'is_private' => filter_var($request->input('is_private'), FILTER_VALIDATE_BOOLEAN),
                            'original_name' => $fileName,
                            'mime_type' => $mimeType,
                            'size' => $fileSize,
                            'uploaded_at' => now()->toISOString()
                        ]
                    );
                } else {
                    // Use Spatie Media Library directly for non-video files
                    $spatieMedia = $user->addMedia($file->getPathname())
                        ->usingName($request->input('title'))
                        ->usingFileName($fileName)
                        ->withCustomProperties([
                            'media_type' => $mediaType,
                            'description' => $request->input('description'),
                            'is_private' => filter_var($request->input('is_private'), FILTER_VALIDATE_BOOLEAN),
                            'original_name' => $fileName,
                            'mime_type' => $mimeType,
                            'size' => $fileSize,
                            'uploaded_at' => now()->toISOString()
                        ])
                        ->toMediaCollection($collectionName, 'public');
                }
                
                Log::info('Media added to Spatie collection successfully', [
                    'spatie_media_id' => $spatieMedia->id,
                    'user_id' => $user->id,
                    'collection' => $collectionName,
                    'file_path' => $spatieMedia->getPath()
                ]);
                
            } catch (\Exception $e) {
                Log::error('Failed to add file to Spatie media collection', [
                    'user_id' => $user->id,
                    'media_type' => $mediaType,
                    'file_name' => $fileName,
                    'error' => $e->getMessage()
                ]);
                
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to process file: ' . $e->getMessage()
                ], 500);
            }

            // Log success
            Log::info('Media uploaded successfully', [
                'spatie_media_id' => $spatieMedia->id,
                'user_id' => $user->id,
                'media_type' => $mediaType,
                'file_size' => $fileSize
            ]);

            // Return JSON response for API calls
            return response()->json([
                'success' => true,
                'message' => ucfirst($mediaType) . ' uploaded successfully',
                'spatie_media_id' => $spatieMedia->id
            ]);

        } catch (\Exception $e) {
            Log::error('Media upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id(),
                'media_type' => $request->input('media_type')
            ]);

            // Return JSON response for API calls
            return response()->json([
                'success' => false,
                'error' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get media for a user with optional filtering using Spatie Media Library
     */
    public function getMedia(Request $request, $userId = null)
    {
        try {
            $targetUserId = $userId ?? Auth::id();
            $targetUser = User::find($targetUserId);

            if (!$targetUser) {
                return response()->json([
                    'success' => false,
                    'error' => 'User not found'
                ], 404);
            }

            // Check if viewing own profile
            $isOwnProfile = Auth::id() == $targetUserId;
            
            // Get media type from request
            $mediaType = $request->input('type');
            
            // Map media types to Spatie collection names
            $collectionMap = [
                'video' => 'videos',
                'image' => 'images',
                'letter' => 'letters',
                'document' => 'documents'
            ];
            
            $collectionName = $collectionMap[$mediaType] ?? $mediaType;
            
            // Get media from Spatie collection
            $mediaQuery = $targetUser->getMedia($collectionName);
            
            // If not own profile, only show public media
            if (!$isOwnProfile) {
                $mediaQuery = $mediaQuery->filter(function ($media) {
                    return !$media->getCustomProperty('is_private', false);
                });
            }
            
            // Transform Spatie media to the expected format
            $mediaData = $mediaQuery->map(function ($media) use ($targetUser) {
                // For videos, use the custom thumbnail method
                $thumbnailUrl = $media->collection_name === 'videos' 
                    ? $targetUser->getVideoThumbnailUrl($media)
                    : $media->getUrl('thumbnail');
                
                return [
                    'id' => $media->id,
                    'name' => $media->name,
                    'file_name' => $media->file_name,
                    'collection_name' => $media->collection_name,
                    'media_type' => $media->getCustomProperty('media_type'),
                    'title' => $media->name,
                    'description' => $media->getCustomProperty('description'),
                    'is_private' => $media->getCustomProperty('is_private', false),
                    'url' => $media->getUrl(),
                    'thumbnail_url' => $thumbnailUrl,
                    'size' => $media->size,
                    'mime_type' => $media->mime_type,
                    'original_name' => $media->getCustomProperty('original_name'),
                    'uploaded_at' => $media->getCustomProperty('uploaded_at'),
                    'created_at' => $media->created_at,
                    'updated_at' => $media->updated_at
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $mediaData->values(),
                'count' => $mediaData->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve media', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'requesting_user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve media'
            ], 500);
        }
    }

    /**
     * Get media by specific type (backward compatibility)
     */
    public function getMediaByType(Request $request, $userId, $type)
    {
        $request->merge(['type' => $type]);
        return $this->getMedia($request, $userId);
    }

    /**
     * Update media details using Spatie Media Library
     */
    public function update(Request $request, $mediaId)
    {
        try {
            // Find the Spatie media by ID
            $spatieMedia = SpatieMedia::find($mediaId);
            
            if (!$spatieMedia) {
                return response()->json([
                    'success' => false,
                    'error' => 'Media not found'
                ], 404);
            }
            
            // Check ownership by checking if the media belongs to the authenticated user
            $user = Auth::user();
            $userMedia = $user->getMedia($spatieMedia->collection_name);
            $isOwner = $userMedia->contains('id', $mediaId);
            
            if (!$isOwner) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 401);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'is_private' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // Update Spatie media custom properties
            $spatieMedia->setCustomProperty('description', $request->input('description'));
            $spatieMedia->setCustomProperty('is_private', filter_var($request->input('is_private'), FILTER_VALIDATE_BOOLEAN));
            $spatieMedia->name = $request->input('title');
            $spatieMedia->save();

            // Log success
            Log::info('Media updated successfully', [
                'media_id' => $spatieMedia->id,
                'user_id' => Auth::id(),
                'collection' => $spatieMedia->collection_name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Media updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Media update failed', [
                'error' => $e->getMessage(),
                'media_id' => $mediaId,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete media and associated files using Spatie Media Library
     */
    public function destroy($mediaId)
    {
        try {
            // Find the Spatie media by ID
            $spatieMedia = SpatieMedia::find($mediaId);
            
            if (!$spatieMedia) {
                return response()->json([
                    'success' => false,
                    'error' => 'Media not found'
                ], 404);
            }
            
            // Check ownership by checking if the media belongs to the authenticated user
            $user = Auth::user();
            $userMedia = $user->getMedia($spatieMedia->collection_name);
            $isOwner = $userMedia->contains('id', $mediaId);
            
            if (!$isOwner) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized'
                ], 401);
            }

            $collectionName = $spatieMedia->collection_name;

            // Delete Spatie media (this will automatically delete associated files)
            $spatieMedia->delete();

            // Log success
            Log::info('Media deleted successfully', [
                'media_id' => $mediaId,
                'user_id' => Auth::id(),
                'collection' => $collectionName
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Media deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Media deletion failed', [
                'error' => $e->getMessage(),
                'media_id' => $mediaId,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Deletion failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get media statistics for a user
     */
    public function getStats($userId = null)
    {
        try {
            $targetUserId = $userId ?? Auth::id();
            $targetUser = User::find($targetUserId);

            if (!$targetUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Get counts by type using Spatie Media Library
            $stats = [
                'videos' => $targetUser->getMedia('videos')->count(),
                'images' => $targetUser->getMedia('images')->count(),
                'letters' => $targetUser->getMedia('letters')->count(),
                'documents' => $targetUser->getMedia('documents')->count(),
            ];

            // Get total file size from all collections
            $totalSize = 0;
            foreach (['videos', 'images', 'letters', 'documents'] as $collection) {
                $totalSize += $targetUser->getMedia($collection)->sum('size');
            }

            // Log request
            Log::info('Media stats retrieved', [
                'target_user_id' => $targetUserId,
                'requesting_user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'user_id' => $targetUserId,
                    'counts' => $stats,
                    'total_size' => $this->formatBytes($totalSize),
                    'total_size_bytes' => $totalSize
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve media stats', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve media stats'
            ], 500);
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        if ($bytes <= 0) return '0 B';

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $base = log($bytes, 1024);
        $unit = $units[floor($base)];

        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $unit;
    }
}
