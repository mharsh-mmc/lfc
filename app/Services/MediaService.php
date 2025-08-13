<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class MediaService
{
    /**
     * Upload media to a specific collection with validation and limits
     */
    public function uploadMedia(
        User $user,
        UploadedFile $file,
        string $collection,
        array $customProperties = [],
        array $validationRules = []
    ): Media {
        // Check collection limits
        $this->validateCollectionLimit($user, $collection);
        
        // Validate file
        $this->validateFile($file, $validationRules);
        
        try {
            // Add media to collection with custom properties
            $media = $user->addMedia($file)
                ->usingName($customProperties['title'] ?? $file->getClientOriginalName())
                ->withCustomProperties($customProperties)
                ->toMediaCollection($collection);
            
            // Process media based on type
            $this->processMedia($media, $collection);
            
            Log::info('Media uploaded successfully', [
                'user_id' => $user->id,
                'media_id' => $media->id,
                'collection' => $collection,
                'file_name' => $media->file_name,
                'size' => $media->size
            ]);
            
            return $media;
            
        } catch (FileDoesNotExist $e) {
            Log::error('File does not exist during upload', [
                'user_id' => $user->id,
                'collection' => $collection,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('File upload failed: File not found');
            
        } catch (FileIsTooBig $e) {
            Log::error('File too big during upload', [
                'user_id' => $user->id,
                'collection' => $collection,
                'file_size' => $file->getSize(),
                'max_size' => config('media-library.max_file_size')
            ]);
            throw new \Exception('File upload failed: File too large');
            
        } catch (\Exception $e) {
            Log::error('Media upload failed', [
                'user_id' => $user->id,
                'collection' => $collection,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    
    /**
     * Validate collection limits for user
     */
    private function validateCollectionLimit(User $user, string $collection): void
    {
        $limits = [
            'videos' => 20,
            'images' => 50,
            'documents' => 20,
            'profile-photos' => 1,
            'banners' => 1,
        ];
        
        if (!isset($limits[$collection])) {
            return; // No limit for this collection
        }
        
        $currentCount = $user->getMedia($collection)->count();
        $limit = $limits[$collection];
        
        // For single-file collections, clear existing media first
        if ($limit === 1 && $currentCount >= 1) {
            Log::info('Clearing existing media collection', [
                'user_id' => $user->id,
                'collection' => $collection,
                'current_count' => $currentCount
            ]);
            
            $user->clearMediaCollection($collection);
            
            // Verify the collection was cleared
            $newCount = $user->getMedia($collection)->count();
            Log::info('Media collection cleared', [
                'user_id' => $user->id,
                'collection' => $collection,
                'new_count' => $newCount
            ]);
            
            return; // Allow the upload to proceed
        }
        
        if ($currentCount >= $limit) {
            throw new \Exception("You have reached the maximum limit of {$limit} items for {$collection}. Please delete some items before uploading new ones.");
        }
    }
    
    /**
     * Validate uploaded file
     */
    private function validateFile(UploadedFile $file, array $validationRules): void
    {
        $validator = \Illuminate\Support\Facades\Validator::make(
            ['file' => $file],
            ['file' => $validationRules]
        );
        
        if ($validator->fails()) {
            throw new \Exception('File validation failed: ' . $validator->errors()->first());
        }
    }
    
    /**
     * Process media based on collection type
     */
    private function processMedia(Media $media, string $collection): void
    {
        switch ($collection) {
            case 'videos':
                $this->processVideo($media);
                break;
            case 'images':
                $this->processImage($media);
                break;
            case 'documents':
                $this->processDocument($media);
                break;
            case 'profile-photos':
                $this->processProfilePhoto($media);
                break;
            case 'banners':
                $this->processBanner($media);
                break;
        }
    }
    
    /**
     * Process video uploads
     */
    private function processVideo(Media $media): void
    {
        // Generate thumbnail and extract duration
        $this->generateVideoThumbnail($media);
        $this->extractVideoDuration($media);
    }
    
    /**
     * Process image uploads
     */
    private function processImage(Media $media): void
    {
        // Images are automatically processed by Media Library conversions
        // Additional processing can be added here if needed
    }
    
    /**
     * Process document uploads
     */
    private function processDocument(Media $media): void
    {
        // Extract PDF page count if it's a PDF
        if ($media->mime_type === 'application/pdf') {
            $this->extractPdfPageCount($media);
        }
    }
    
    /**
     * Process profile photo uploads
     */
    private function processProfilePhoto(Media $media): void
    {
        // Profile photos are automatically processed by Media Library
        // Update user's profile_photo_path
        $user = $media->model;
        $user->update(['profile_photo_path' => $media->getPathRelativeToRoot()]);
    }
    
    /**
     * Process banner uploads
     */
    private function processBanner(Media $media): void
    {
        // Update user's banner_path
        $user = $media->model;
        $user->update(['banner_path' => $media->getPathRelativeToRoot()]);
    }
    
    /**
     * Generate video thumbnail using FFmpeg
     */
    private function generateVideoThumbnail(Media $media): void
    {
        try {
            $videoPath = $media->getPath();
            $userId = $media->model->id;
            
            // Create user-specific thumbnail directory
            $thumbnailDir = storage_path('app/public/users/' . $userId . '/videos/thumbnails/');
            if (!file_exists($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }
            
            $thumbnailPath = $thumbnailDir . $media->id . '.jpg';
            
            // Use FFmpeg to generate thumbnail
            if ($this->isFFmpegAvailable()) {
                $ffmpegPath = env('FFMPEG_PATH') ?: 'ffmpeg';
                
                // First, get video duration to choose better thumbnail position
                $duration = $this->getVideoDuration($videoPath);
                $thumbnailTime = $this->getOptimalThumbnailTime($duration);
                
                // Build FFmpeg command with better settings
                $ffmpegCommand = escapeshellcmd($ffmpegPath) . 
                               " -i " . escapeshellarg($videoPath) . 
                               " -ss " . $thumbnailTime . 
                               " -vframes 1 -vf scale=320:240:force_original_aspect_ratio=decrease,pad=320:240:(ow-iw)/2:(oh-ih)/2" . 
                               " -q:v 2 " . // High quality
                               escapeshellarg($thumbnailPath);
                
                Log::info('Executing FFmpeg thumbnail command', [
                    'media_id' => $media->id,
                    'command' => $ffmpegCommand,
                    'video_duration' => $duration,
                    'thumbnail_time' => $thumbnailTime
                ]);
                
                // Execute command
                $result = $this->executeCommand($ffmpegCommand);
                
                if ($result['return_code'] === 0 && file_exists($thumbnailPath) && filesize($thumbnailPath) > 1000) {
                    $media->setCustomProperty('thumbnail_path', 'users/' . $userId . '/videos/thumbnails/' . $media->id . '.jpg');
                    $media->save();
                    
                    Log::info('Video thumbnail generated successfully', [
                        'media_id' => $media->id,
                        'thumbnail_path' => $thumbnailPath,
                        'file_size' => filesize($thumbnailPath),
                        'output' => $result['output']
                    ]);
                } else {
                    Log::error('FFmpeg thumbnail generation failed', [
                        'media_id' => $media->id,
                        'return_code' => $result['return_code'],
                        'output' => $result['output'],
                        'command' => $ffmpegCommand,
                        'thumbnail_exists' => file_exists($thumbnailPath),
                        'thumbnail_size' => file_exists($thumbnailPath) ? filesize($thumbnailPath) : 0
                    ]);
                    
                    // Fallback to default thumbnail
                    $this->createDefaultThumbnail($media, $userId);
                }
            } else {
                Log::warning('FFmpeg not available for thumbnail generation', [
                    'media_id' => $media->id,
                    'ffmpeg_path' => env('FFMPEG_PATH') ?: 'ffmpeg'
                ]);
                
                // Fallback to default thumbnail
                $this->createDefaultThumbnail($media, $userId);
            }
            
        } catch (\Exception $e) {
            Log::error('Error generating video thumbnail', [
                'media_id' => $media->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback to default thumbnail
            $this->createDefaultThumbnail($media, $userId);
        }
    }
    
    /**
     * Get video duration using FFprobe
     */
    private function getVideoDuration(string $videoPath): float
    {
        try {
            if ($this->isFFprobeAvailable()) {
                $ffprobePath = env('FFPROBE_PATH') ?: 'ffprobe';
                $command = escapeshellcmd($ffprobePath) . 
                          " -v quiet -show_entries format=duration -of csv=p=0 " . 
                          escapeshellarg($videoPath);
                
                $duration = shell_exec($command);
                return (float) trim($duration);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to get video duration', ['error' => $e->getMessage()]);
        }
        
        return 0.0;
    }
    
    /**
     * Get optimal thumbnail time based on video duration
     */
    private function getOptimalThumbnailTime(float $duration): string
    {
        if ($duration <= 0) {
            return '00:00:01'; // Default to 1 second
        }
        
        // Choose thumbnail time based on video duration
        if ($duration <= 10) {
            // For short videos, take frame from middle
            $time = $duration / 2;
        } elseif ($duration <= 60) {
            // For medium videos, take frame from 25% mark
            $time = $duration * 0.25;
        } else {
            // For long videos, take frame from 10% mark
            $time = $duration * 0.1;
        }
        
        // Ensure minimum time of 1 second
        $time = max(1, $time);
        
        // Convert to HH:MM:SS format
        $hours = floor($time / 3600);
        $minutes = floor(($time % 3600) / 60);
        $seconds = floor($time % 60);
        
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
    
    /**
     * Create a default thumbnail when FFmpeg is not available
     */
    private function createDefaultThumbnail(Media $media, int $userId): void
    {
        try {
            $thumbnailDir = storage_path('app/public/users/' . $userId . '/videos/thumbnails/');
            if (!file_exists($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }
            
            $thumbnailPath = $thumbnailDir . $media->id . '.jpg';
            
            // Always use our thumbnail generation methods instead of copying default file
            if (extension_loaded('gd')) {
                $this->createGDThumbnail($thumbnailPath);
            } else {
                $this->createSimpleThumbnail($thumbnailPath);
            }
            
            $media->setCustomProperty('thumbnail_path', 'users/' . $userId . '/videos/thumbnails/' . $media->id . '.jpg');
            $media->save();
            
            Log::info('Default thumbnail created successfully', [
                'media_id' => $media->id,
                'thumbnail_path' => $thumbnailPath,
                'file_size' => file_exists($thumbnailPath) ? filesize($thumbnailPath) : 0,
                'gd_available' => extension_loaded('gd')
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error creating default thumbnail', [
                'media_id' => $media->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'gd_available' => extension_loaded('gd')
            ]);
            
            // Try to create a simple fallback using a different approach
            $this->createSimpleFallbackThumbnail($media, $userId);
        }
    }
    
    /**
     * Create thumbnail using GD extension
     */
    private function createGDThumbnail(string $thumbnailPath): void
    {
        $width = 320;
        $height = 240;
        
        // Create image resource
        $image = imagecreatetruecolor($width, $height);
        if (!$image) {
            throw new \Exception('Failed to create image resource');
        }
        
        // Define colors
        $bgColor = imagecolorallocate($image, 52, 73, 94); // Dark blue-gray
        $textColor = imagecolorallocate($image, 255, 255, 255); // White
        $accentColor = imagecolorallocate($image, 41, 128, 185); // Blue accent
        
        // Fill background
        imagefill($image, 0, 0, $bgColor);
        
        // Draw a play button icon
        $centerX = $width / 2;
        $centerY = $height / 2;
        
        // Draw play triangle
        $triangleSize = 40;
        $points = [
            $centerX - $triangleSize/2, $centerY - $triangleSize/2,
            $centerX - $triangleSize/2, $centerY + $triangleSize/2,
            $centerX + $triangleSize/2, $centerY
        ];
        imagefilledpolygon($image, $points, 3, $accentColor);
        
        // Add text
        $fontSize = 4;
        $text = 'VIDEO';
        $textWidth = imagefontwidth($fontSize) * strlen($text);
        $textX = ($width - $textWidth) / 2;
        $textY = $centerY + $triangleSize + 20;
        
        imagestring($image, $fontSize, $textX, $textY, $text, $textColor);
        
        // Save as JPEG with high quality
        $success = imagejpeg($image, $thumbnailPath, 95);
        imagedestroy($image);
        
        if (!$success) {
            throw new \Exception('Failed to save JPEG image');
        }
        
        // Verify the file was created and is valid
        if (!file_exists($thumbnailPath) || filesize($thumbnailPath) === 0) {
            throw new \Exception('Thumbnail file was not created or is empty');
        }
        
        // Verify it's a valid JPEG by checking file header
        $fileContent = file_get_contents($thumbnailPath);
        if (strlen($fileContent) < 2 || substr($fileContent, 0, 2) !== "\xFF\xD8") {
            throw new \Exception('Generated file is not a valid JPEG');
        }
    }
    
    /**
     * Create a simple thumbnail without GD (fallback)
     */
    private function createSimpleThumbnail(string $thumbnailPath): void
    {
        // Create a minimal valid JPEG file (1x1 pixel gray)
        $jpegData = "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x01\x00H\x00H\x00\x00\xFF\xDB\x00C\x00\x08\x06\x06\x07\x06\x05\x08\x07\x07\x07\t\t\x08\n\x0C\x14\r\x0C\x0B\x0B\x0C\x19\x12\x13\x0F\x14\x1D\x1A\x1F\x1E\x1D\x1A\x1C\x1C $.' \",#\x1C\x1C(7),01444\x1F'9=82<.342\xFF\xC0\x00\x11\x08\x00\x01\x00\x01\x01\x01\x11\x00\x02\x11\x01\x03\x11\x01\xFF\xC4\x00\x14\x00\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x08\xFF\xC4\x00\x14\x10\x01\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xFF\xDA\x00\x0C\x03\x01\x00\x02\x11\x03\x11\x00\x3F\x00\x8A\xFF\xD9";
        
        file_put_contents($thumbnailPath, $jpegData);
        
        if (!file_exists($thumbnailPath) || filesize($thumbnailPath) === 0) {
            throw new \Exception('Failed to create simple thumbnail');
        }
    }
    
    /**
     * Create a very simple fallback thumbnail as last resort
     */
    private function createSimpleFallbackThumbnail(Media $media, int $userId): void
    {
        try {
            $thumbnailDir = storage_path('app/public/users/' . $userId . '/videos/thumbnails/');
            if (!file_exists($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }
            
            $thumbnailPath = $thumbnailDir . $media->id . '.jpg';
            
            // Create a minimal 1x1 pixel JPEG as fallback
            $image = imagecreatetruecolor(1, 1);
            $color = imagecolorallocate($image, 128, 128, 128);
            imagefill($image, 0, 0, $color);
            imagejpeg($image, $thumbnailPath, 100);
            imagedestroy($image);
            
            $media->setCustomProperty('thumbnail_path', 'users/' . $userId . '/videos/thumbnails/' . $media->id . '.jpg');
            $media->save();
            
            Log::info('Simple fallback thumbnail created', [
                'media_id' => $media->id,
                'thumbnail_path' => $thumbnailPath
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to create even simple fallback thumbnail', [
                'media_id' => $media->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Extract video duration using FFprobe
     */
    private function extractVideoDuration(Media $media): void
    {
        try {
            $videoPath = $media->getPath();
            
            if ($this->isFFprobeAvailable()) {
                $ffprobePath = env('FFPROBE_PATH') ?: 'ffprobe';
                
                // Build FFprobe command
                $ffprobeCommand = escapeshellcmd($ffprobePath) . 
                                " -v quiet -show_entries format=duration -of csv=p=0 " . 
                                escapeshellarg($videoPath);
                
                Log::info('Executing FFprobe command', [
                    'media_id' => $media->id,
                    'command' => $ffprobeCommand
                ]);
                
                // Execute command
                $result = $this->executeCommand($ffprobeCommand);
                
                if ($result['return_code'] === 0 && $result['output'] && is_numeric(trim($result['output']))) {
                    $duration = (int) trim($result['output']);
                    $media->setCustomProperty('duration', $duration);
                    $media->save();
                    
                    Log::info('Video duration extracted', [
                        'media_id' => $media->id,
                        'duration' => $duration,
                        'output' => $result['output']
                    ]);
                } else {
                    Log::error('FFprobe duration extraction failed', [
                        'media_id' => $media->id,
                        'return_code' => $result['return_code'],
                        'output' => $result['output'],
                        'command' => $ffprobeCommand
                    ]);
                }
            } else {
                Log::warning('FFprobe not available for duration extraction', [
                    'media_id' => $media->id,
                    'ffprobe_path' => env('FFPROBE_PATH') ?: 'ffprobe'
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Error extracting video duration', [
                'media_id' => $media->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * Extract PDF page count
     */
    private function extractPdfPageCount(Media $media): void
    {
        try {
            $pdfPath = $media->getPath();
            
            // Use pdftk or similar tool to extract page count
            // For now, we'll set a default value
            $media->setCustomProperty('page_count', 1);
            $media->save();
            
        } catch (\Exception $e) {
            Log::error('Error extracting PDF page count', [
                'media_id' => $media->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Check if FFmpeg is available
     */
    private function isFFmpegAvailable(): bool
    {
        $ffmpegPath = env('FFMPEG_PATH') ?: 'ffmpeg';
        
        // Test if FFmpeg is available by running a simple command
        $testCommand = escapeshellcmd($ffmpegPath) . ' -version 2>&1';
        $output = shell_exec($testCommand);
        
        $isAvailable = !empty($output) && strpos($output, 'ffmpeg version') !== false;
        
        Log::info('FFmpeg availability check', [
            'ffmpeg_path' => $ffmpegPath,
            'is_available' => $isAvailable,
            'output_length' => strlen($output ?? ''),
            'output_preview' => substr($output ?? '', 0, 100)
        ]);
        
        return $isAvailable;
    }
    
    /**
     * Check if FFprobe is available
     */
    private function isFFprobeAvailable(): bool
    {
        $ffprobePath = env('FFPROBE_PATH') ?: 'ffprobe';
        
        // Test if FFprobe is available by running a simple command
        $testCommand = escapeshellcmd($ffprobePath) . ' -version 2>&1';
        $output = shell_exec($testCommand);
        
        return !empty($output) && strpos($output, 'ffprobe version') !== false;
    }
    
    /**
     * Execute command and get return code
     */
    private function executeCommand(string $command): array
    {
        $output = shell_exec($command . ' 2>&1');
        $returnCode = $this->getLastReturnCode();
        
        return [
            'output' => $output,
            'return_code' => $returnCode
        ];
    }
    
    /**
     * Get the last return code from shell_exec
     */
    private function getLastReturnCode(): int
    {
        // On Windows, we need to use a different approach
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // For Windows, we'll use exec to get the return code
            return 0; // We'll handle this differently
        }
        
        // For Unix-like systems
        return $GLOBALS['return_code'] ?? 0;
    }
    
    /**
     * Get media collection for user with pagination
     */
    public function getUserMedia(User $user, string $collection, int $perPage = 20): array
    {
        $media = $user->getMedia($collection)->paginate($perPage);
        
        return [
            'data' => $media->items(),
            'pagination' => [
                'current_page' => $media->currentPage(),
                'last_page' => $media->lastPage(),
                'per_page' => $media->perPage(),
                'total' => $media->total(),
            ]
        ];
    }
    
    /**
     * Delete media with cleanup
     */
    public function deleteMedia(User $user, int $mediaId, string $collection): bool
    {
        $media = $user->getMedia($collection)->find($mediaId);
        
        if (!$media) {
            throw new \Exception('Media not found');
        }
        
        try {
            $media->delete();
            
            Log::info('Media deleted successfully', [
                'user_id' => $user->id,
                'media_id' => $mediaId,
                'collection' => $collection
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            Log::error('Failed to delete media', [
                'user_id' => $user->id,
                'media_id' => $mediaId,
                'collection' => $collection,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Update media custom properties
     */
    public function updateMedia(User $user, int $mediaId, string $collection, array $customProperties): Media
    {
        $media = $user->getMedia($collection)->find($mediaId);
        
        if (!$media) {
            throw new \Exception('Media not found');
        }
        
        try {
            foreach ($customProperties as $key => $value) {
                $media->setCustomProperty($key, $value);
            }
            
            $media->save();
            
            Log::info('Media updated successfully', [
                'user_id' => $user->id,
                'media_id' => $mediaId,
                'collection' => $collection,
                'properties' => $customProperties
            ]);
            
            return $media;
            
        } catch (\Exception $e) {
            Log::error('Failed to update media', [
                'user_id' => $user->id,
                'media_id' => $mediaId,
                'collection' => $collection,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
} 