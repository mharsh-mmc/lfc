# Scalable Media System for LiveForever

This document describes the comprehensive media handling system designed to support 1,000,000+ users across all profile tab modules.

## Overview

The new media system replaces all previous media upload handling with a unified Spatie Media Library approach, providing:

- **Scalability**: Designed for 1M+ users
- **Performance**: Optimized database queries and file handling
- **Flexibility**: Support for multiple media types and collections
- **Reliability**: Comprehensive error handling and validation
- **Monitoring**: Built-in logging and statistics

## Architecture

### Core Components

1. **MediaService** (`app/Services/MediaService.php`)
   - Centralized media handling logic
   - Validation and processing
   - Error handling and logging

2. **Media API Controller** (`app/Http/Controllers/Api/MediaApiController.php`)
   - RESTful API endpoints
   - Pagination support
   - Bulk operations

3. **Enhanced User Model** (`app/Models/User.php`)
   - Spatie Media Library integration
   - Custom media collections
   - Automatic conversions

4. **Configuration** (`config/media-settings.php`)
   - Scalable settings
   - File size limits
   - Collection limits

## Media Collections

### 1. Videos Collection
- **Limit**: 20 videos per user
- **Max Size**: 1GB per video
- **Formats**: MP4, AVI, MOV, WMV, FLV, WebM, MKV, 3GP
- **Features**: Automatic thumbnail generation, duration extraction

### 2. Images Collection
- **Limit**: 50 images per user
- **Max Size**: 100MB per image
- **Formats**: JPEG, PNG, WebP, GIF
- **Features**: Automatic thumbnail generation, compression

### 3. Documents Collection
- **Limit**: 20 documents per user
- **Max Size**: 50MB per document
- **Formats**: PDF
- **Features**: Page count extraction

### 4. Profile Photos Collection
- **Limit**: 1 photo per user
- **Max Size**: 10MB per photo
- **Formats**: JPEG, PNG, WebP
- **Features**: Automatic resizing, high quality

### 5. Banners Collection
- **Limit**: 1 banner per user
- **Max Size**: 20MB per banner
- **Formats**: JPEG, PNG, WebP
- **Features**: Automatic resizing, optimized dimensions

## API Endpoints

### Media Upload
```http
POST /api/media/upload
Content-Type: multipart/form-data

{
    "file": [binary],
    "collection": "videos|images|documents|profile-photos|banners",
    "caption": "Optional caption"
}
```

### Get User Media
```http
GET /api/media/users/{userId}/{collection}?per_page=20&page=1
```

### Update Media
```http
PUT /api/media/{mediaId}
{
    "collection": "videos",
    "caption": "Updated caption"
}
```

### Delete Media
```http
DELETE /api/media/{mediaId}
{
    "collection": "videos"
}
```

### Bulk Delete
```http
DELETE /api/media/bulk
{
    "collection": "videos",
    "media_ids": [1, 2, 3]
}
```

### Get Media Statistics
```http
GET /api/media/users/{userId}/stats
```

## Database Optimizations

### Indexes Added
- Media table indexes for model, collection, disk, timestamps
- User table indexes for profile photos and banners
- Custom properties index (MySQL 5.7+)

### New Tables
- `media_statistics`: Cached statistics for performance
- `media_upload_queue`: Queue for large file processing

## Configuration

### Environment Variables
```env
# Media Storage
MEDIA_DISK=public
MEDIA_BACKUP_DISK=s3
MEDIA_CACHE_DISK=local

# CDN Settings
MEDIA_CDN_ENABLED=false
MEDIA_CDN_URL=

# Processing
MEDIA_QUEUE=media-processing
MEDIA_COMPRESS_IMAGES=true
MEDIA_COMPRESS_VIDEOS=false

# FFmpeg
FFMPEG_ENABLED=false
FFMPEG_PATH=/usr/bin/ffmpeg
FFPROBE_PATH=/usr/bin/ffprobe
FFMPEG_THREADS=2

# Rate Limiting
MEDIA_UPLOADS_PER_MINUTE=10
MEDIA_UPLOADS_PER_HOUR=100
MEDIA_UPLOADS_PER_DAY=1000

# Monitoring
MEDIA_MONITORING_ENABLED=true
MEDIA_CLEANUP_ENABLED=true
```

## Performance Features

### 1. Pagination
- Efficient pagination for large media collections
- Configurable page sizes
- Cursor-based pagination for better performance

### 2. Caching
- Media URL caching
- Thumbnail caching
- Statistics caching

### 3. Queue Processing
- Background processing for large files
- Retry mechanisms
- Progress tracking

### 4. CDN Support
- Configurable CDN integration
- Automatic URL generation
- Fallback to local storage

## Security Features

### 1. File Validation
- MIME type validation
- File extension validation
- File size limits
- Malware scanning (configurable)

### 2. Access Control
- User-based media access
- Collection-based permissions
- Secure file URLs

### 3. Rate Limiting
- Per-user upload limits
- Time-based restrictions
- Abuse prevention

## Monitoring and Logging

### 1. Upload Monitoring
- Success/failure tracking
- Performance metrics
- Error logging

### 2. Storage Monitoring
- Disk usage tracking
- Storage alerts
- Cleanup automation

### 3. Processing Monitoring
- Queue monitoring
- Processing time tracking
- Error reporting

## Migration Guide

### From Old System
1. **Backup existing media files**
2. **Run migration**: `php artisan migrate`
3. **Update frontend components** to use new API endpoints
4. **Test thoroughly** before production deployment

### Frontend Integration
```javascript
// Upload media
const uploadMedia = async (file, collection, caption = '') => {
    const formData = new FormData();
    formData.append('file', file);
    formData.append('collection', collection);
    formData.append('caption', caption);

    const response = await fetch('/api/media/upload', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    });

    return response.json();
};

// Get user media with pagination
const getUserMedia = async (userId, collection, page = 1) => {
    const response = await fetch(`/api/media/users/${userId}/${collection}?page=${page}`);
    return response.json();
};
```

## Troubleshooting

### Common Issues

1. **Upload Fails**
   - Check file size limits
   - Verify MIME type
   - Check disk space
   - Review error logs

2. **Thumbnails Not Generating**
   - Verify FFmpeg installation
   - Check FFmpeg path configuration
   - Review processing queue

3. **Performance Issues**
   - Enable caching
   - Configure CDN
   - Optimize database queries
   - Monitor server resources

### Debug Commands
```bash
# Check media statistics
php artisan fix:profile-photos

# Clear media cache
php artisan cache:clear

# Check queue status
php artisan queue:work

# Monitor storage
php artisan storage:link
```

## Future Enhancements

### Planned Features
1. **AI-powered content analysis**
2. **Automatic content moderation**
3. **Advanced compression algorithms**
4. **Multi-region storage**
5. **Real-time processing**

### Scalability Improvements
1. **Microservices architecture**
2. **Distributed storage**
3. **Load balancing**
4. **Auto-scaling**

## Support

For issues and questions:
1. Check the logs in `storage/logs/laravel.log`
2. Review the configuration in `config/media-settings.php`
3. Test with the provided debug commands
4. Contact the development team

---

**Version**: 1.0.0  
**Last Updated**: August 2025  
**Compatibility**: Laravel 11+, PHP 8.4+ 