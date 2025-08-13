<?php

namespace App\Http\Controllers;

use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PhotoController extends Controller
{
    protected MediaService $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * Upload a photo file (image)
     */
    public function upload(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'photo' => 'required|file|mimes:jpg,jpeg,png,gif,webp|max:10485760', // 10MB max
            'caption' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator->errors());
        }

        try {
            // Check if user has reached the limit (50 photos)
            $existingPhotos = $user->getMedia('images')->count();
            if ($existingPhotos >= 50) {
                $errorMessage = 'You have reached the maximum limit of 50 photos. Please delete some photos before uploading new ones.';
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 422);
                }
                return back()->withErrors(['message' => $errorMessage]);
            }

            $customProperties = [
                'caption' => $request->input('caption', ''),
                'type' => 'uploaded',
            ];

            $media = $this->mediaService->uploadMedia(
                $user,
                $request->file('photo'),
                'images',
                $customProperties,
                [
                    'required',
                    'file',
                    'mimes:jpg,jpeg,png,gif,webp',
                    'max:10485760', // 10MB max
                ]
            );

            $successMessage = 'Photo uploaded successfully';
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'photo' => [
                        'id' => $media->id,
                        'caption' => $media->getCustomProperty('caption'),
                        'url' => $media->getUrl(),
                        'thumbnail' => $media->getUrl('thumbnail'),
                        'file_size' => $media->size,
                        'mime_type' => $media->mime_type,
                        'created_at' => $media->created_at,
                    ]
                ]);
            }
            return back()->with('success', $successMessage);

        } catch (\Exception $e) {
            $errorMessage = 'Failed to upload photo: ' . $e->getMessage();
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ], 500);
            }
            return back()->withErrors(['message' => $errorMessage]);
        }
    }
} 