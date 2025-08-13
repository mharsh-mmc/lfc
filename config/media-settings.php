<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Media Settings for Scalability (1M+ Users)
    |--------------------------------------------------------------------------
    |
    | This configuration file contains settings optimized for handling
    | media uploads and processing for a large user base.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | File Size Limits
    |--------------------------------------------------------------------------
    |
    | Maximum file sizes for different media types
    |
    */
    'file_size_limits' => [
        'videos' => 1024 * 1024 * 1024, // 1GB
        'images' => 100 * 1024 * 1024,   // 100MB
        'documents' => 50 * 1024 * 1024,  // 50MB
        'profile_photos' => 10 * 1024 * 1024, // 10MB
        'banners' => 20 * 1024 * 1024,    // 20MB
    ],

    /*
    |--------------------------------------------------------------------------
    | Collection Limits
    |--------------------------------------------------------------------------
    |
    | Maximum number of items per collection per user
    |
    */
    'collection_limits' => [
        'videos' => 20,
        'images' => 50,
        'documents' => 20,
        'profile_photos' => 1,
        'banners' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed MIME Types
    |--------------------------------------------------------------------------
    |
    | Allowed MIME types for each collection
    |
    */
    'allowed_mime_types' => [
        'videos' => [
            'video/mp4',
            'video/avi',
            'video/quicktime',
            'video/x-msvideo',
            'video/x-ms-wmv',
            'video/x-flv',
            'video/webm',
            'video/x-matroska',
            'video/3gpp',
            'audio/x-hx-aac-adts',
            'application/octet-stream',
        ],
        'images' => [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/webp',
            'image/gif',
        ],
        'documents' => [
            'application/pdf',
        ],
        'profile_photos' => [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/webp',
        ],
        'banners' => [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/webp',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed File Extensions
    |--------------------------------------------------------------------------
    |
    | Allowed file extensions for each collection
    |
    */
    'allowed_extensions' => [
        'videos' => ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv', '3gp'],
        'images' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
        'documents' => ['pdf'],
        'profile_photos' => ['jpg', 'jpeg', 'png', 'webp'],
        'banners' => ['jpg', 'jpeg', 'png', 'webp'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Media Processing Settings
    |--------------------------------------------------------------------------
    |
    | Settings for media processing and conversions
    |
    */
    'processing' => [
        'queue_name' => env('MEDIA_QUEUE', 'media-processing'),
        'max_processing_time' => 300, // 5 minutes
        'retry_attempts' => 3,
        'chunk_size' => 1024 * 1024, // 1MB chunks for large files
    ],

    /*
    |--------------------------------------------------------------------------
    | Thumbnail Settings
    |--------------------------------------------------------------------------
    |
    | Settings for thumbnail generation
    |
    */
    'thumbnails' => [
        'videos' => [
            'width' => 320,
            'height' => 240,
            'quality' => 80,
        ],
        'images' => [
            'width' => 320,
            'height' => 240,
            'quality' => 80,
        ],
        'profile_photos' => [
            'width' => 200,
            'height' => 200,
            'quality' => 90,
        ],
        'banners' => [
            'width' => 1200,
            'height' => 400,
            'quality' => 85,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Settings
    |--------------------------------------------------------------------------
    |
    | Storage configuration for different environments
    |
    */
    'storage' => [
        'default_disk' => env('MEDIA_DISK', 'public'),
        'backup_disk' => env('MEDIA_BACKUP_DISK', 's3'),
        'cache_disk' => env('MEDIA_CACHE_DISK', 'local'),
        
        // CDN settings
        'cdn_enabled' => env('MEDIA_CDN_ENABLED', false),
        'cdn_url' => env('MEDIA_CDN_URL'),
        
        // Compression settings
        'compress_images' => env('MEDIA_COMPRESS_IMAGES', true),
        'compress_videos' => env('MEDIA_COMPRESS_VIDEOS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | FFmpeg Settings
    |--------------------------------------------------------------------------
    |
    | Settings for video processing with FFmpeg
    |
    */
    'ffmpeg' => [
        'enabled' => env('FFMPEG_ENABLED', false),
        'path' => env('FFMPEG_PATH', 'ffmpeg'),
        'ffprobe_path' => env('FFPROBE_PATH', 'ffprobe'),
        'timeout' => 300, // 5 minutes
        'threads' => env('FFMPEG_THREADS', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Settings for media caching
    |
    */
    'cache' => [
        'enabled' => env('MEDIA_CACHE_ENABLED', true),
        'ttl' => 3600, // 1 hour
        'prefix' => 'media_cache',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limiting settings for media uploads
    |
    */
    'rate_limiting' => [
        'uploads_per_minute' => env('MEDIA_UPLOADS_PER_MINUTE', 10),
        'uploads_per_hour' => env('MEDIA_UPLOADS_PER_HOUR', 100),
        'uploads_per_day' => env('MEDIA_UPLOADS_PER_DAY', 1000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Settings
    |--------------------------------------------------------------------------
    |
    | Settings for media monitoring and alerts
    |
    */
    'monitoring' => [
        'enabled' => env('MEDIA_MONITORING_ENABLED', true),
        'log_failed_uploads' => true,
        'log_processing_errors' => true,
        'alert_on_storage_full' => true,
        'alert_threshold_percent' => 80,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cleanup Settings
    |--------------------------------------------------------------------------
    |
    | Settings for automatic cleanup of orphaned files
    |
    */
    'cleanup' => [
        'enabled' => env('MEDIA_CLEANUP_ENABLED', true),
        'schedule' => 'daily',
        'delete_orphaned_files' => true,
        'delete_failed_uploads' => true,
        'retention_days' => 30,
    ],
]; 