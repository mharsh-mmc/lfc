# Video Gallery Features

## Overview
The video gallery allows users to upload, view, and manage video content with automatic thumbnail generation and responsive design.

## Features

### ✅ Implemented Features
- **Video Upload**: Support for multiple video formats (MP4, AVI, MOV, WMV, FLV, WebM, MKV, 3GP)
- **Upload Progress Bar**: Real-time progress tracking during video uploads
- **Optional Captions**: Videos can be uploaded without captions and edited later
- **Automatic Thumbnail Generation**: FFmpeg-based thumbnail extraction from videos
- **Video Player**: Modal video player for viewing uploaded videos
- **Responsive Design**: Mobile-friendly grid layout
- **File Limits**: Maximum 20 videos per user
- **Duration Extraction**: Automatic video duration detection

### 🎨 Visual Design
- **Full-Width Gallery**: Utilizes entire screen width
- **Responsive Grid**: Adapts to different screen sizes
- **Modern UI**: Clean, minimalist design with blue accent colors
- **Play Button Overlay**: Always visible play button on thumbnails
- **Hover Effects**: Smooth transitions and interactions

## Setup Instructions

### 1. FFmpeg Installation (Required for Thumbnails)

#### Windows Installation:
1. Download FFmpeg from https://www.gyan.dev/ffmpeg/builds/
2. Extract to `C:\ffmpeg`
3. Add `C:\ffmpeg\bin` to your system PATH
4. Test installation: `ffmpeg -version`

#### Environment Configuration:
Add these lines to your `.env` file:
```env
FFMPEG_PATH=C:\ffmpeg\bin\ffmpeg.exe
FFPROBE_PATH=C:\ffmpeg\bin\ffprobe.exe
```

### 2. Storage Setup
Ensure your storage is properly configured:
```bash
php artisan storage:link
```

### 3. Database Migration
Run migrations to ensure all tables are up to date:
```bash
php artisan migrate
```

## File Structure

### Backend Files
- `app/Http/Controllers/VideoController.php` - Video upload and management
- `app/Models/User.php` - Media collection configuration
- `config/media-library.php` - Media library configuration

### Frontend Files
- `resources/js/components/VideoGrid.vue` - Main video gallery component
- `resources/js/components/VideoPlayer.vue` - Video player modal
- `resources/js/components/VideoUploadModal.vue` - Upload modal with progress
- `resources/js/pages/Profile/DynamicProfile.vue` - Profile page integration

### Assets
- `public/default-video-thumbnail.svg` - Default thumbnail placeholder
- `storage/app/public/thumbnails/` - Generated thumbnails directory

## Usage

### Uploading Videos
1. Click the "+" button in the video gallery
2. Select a video file (max 500MB)
3. Optionally add a caption
4. Monitor upload progress
5. Video will appear in the gallery with generated thumbnail

### Viewing Videos
1. Click on any video thumbnail
2. Video opens in a modal player
3. Use browser controls to play/pause/seek
4. Click outside or press Escape to close

### Editing Videos
1. Click the edit button (pencil icon)
2. Modify video caption
3. Save changes

## Technical Details

### Video Processing
- **Thumbnail Generation**: Extracts frame at 1 second using FFmpeg
- **Duration Extraction**: Uses FFprobe to get video duration
- **File Storage**: Videos stored in `storage/app/public/`
- **Thumbnail Storage**: Thumbnails stored in `storage/app/public/thumbnails/`

### Responsive Breakpoints
- **Mobile**: 1 column (320px+)
- **Small**: 2 columns (640px+)
- **Medium**: 3 columns (768px+)
- **Large**: 4 columns (1024px+)
- **XL**: 5 columns (1280px+)

### Supported Video Formats
- MP4, AVI, MOV, WMV, FLV, WebM, MKV, 3GP
- Maximum file size: 500MB
- Maximum videos per user: 20

## API Endpoints

### Video Management
- `POST /videos` - Upload new video
- `GET /videos/{id}` - Get video details
- `PUT /videos/{id}` - Update video caption
- `DELETE /videos/{id}` - Delete video
- `GET /users/{id}/videos` - Get user's videos

### Response Format
```json
{
  "success": true,
  "video": {
    "id": 1,
    "caption": "Video Title",
    "url": "/storage/videos/video.mp4",
    "thumbnail": "/storage/thumbnails/1.jpg",
    "duration": 120,
    "file_size": 1048576,
    "mime_type": "video/mp4",
    "created_at": "2025-01-27T10:00:00Z"
  }
}
```

## Troubleshooting

### Thumbnails Not Showing
1. **Check FFmpeg Installation**:
   ```bash
   ffmpeg -version
   ffprobe -version
   ```

2. **Verify Environment Variables**:
   ```bash
   php artisan tinker --execute="echo env('FFMPEG_PATH');"
   ```

3. **Check Thumbnails Directory**:
   ```bash
   ls storage/app/public/thumbnails/
   ```

4. **Review Logs**:
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Upload Issues
1. **File Size**: Ensure video is under 500MB
2. **File Format**: Check if format is supported
3. **Storage Permissions**: Ensure storage directory is writable
4. **CSRF Token**: Verify CSRF token is included in requests

### Performance Issues
1. **Large Files**: Consider implementing chunked uploads
2. **Thumbnail Generation**: May take time for large videos
3. **Memory Usage**: Monitor server memory during processing

## Security Considerations

### File Validation
- MIME type checking
- File extension validation
- File size limits
- User-specific storage isolation

### Access Control
- User authentication required
- User can only access their own videos
- CSRF protection on all uploads

### Storage Security
- Files stored outside web root
- Direct access to video files restricted
- Thumbnails served through Laravel routes

## Future Enhancements

### Planned Features
- [ ] Video compression for faster loading
- [ ] Multiple thumbnail generation (different timestamps)
- [ ] Video preview in upload modal
- [ ] Batch upload functionality
- [ ] Video categories/tags
- [ ] Video sharing between users
- [ ] Video comments and likes
- [ ] Advanced video player controls

### Performance Optimizations
- [ ] Lazy loading for large galleries
- [ ] Video streaming for large files
- [ ] CDN integration for video delivery
- [ ] Background job processing for thumbnails
- [ ] Video transcoding for web compatibility

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Review Laravel logs in `storage/logs/`
3. Verify FFmpeg installation and configuration
4. Test with different video formats and sizes 