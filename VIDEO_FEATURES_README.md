# Video Gallery Features

This application now includes a comprehensive video gallery system with the following features:

## ✨ Features

### 🎥 Video Upload
- **Drag & Drop Support**: Upload videos by dragging files directly onto the upload area
- **Progress Tracking**: Real-time upload progress bar with percentage display
- **File Validation**: Automatic validation of file size and format
- **Optional Captions**: Add captions during upload or edit them later
- **Multiple Formats**: Support for MP4, AVI, MOV, WMV, FLV, WebM, MKV, 3GP

### 🖼️ Automatic Thumbnail Generation
- **FFmpeg Integration**: Automatically generates thumbnails from video files
- **Duration Extraction**: Extracts video duration for display
- **Fallback Support**: Uses default thumbnails if FFmpeg is not available
- **High Quality**: 320x240 pixel thumbnails for optimal display

### 🎬 Video Player
- **Modal Player**: Full-screen video player with controls
- **Responsive Design**: Works on desktop and mobile devices
- **Keyboard Support**: Press Escape to close the player
- **Video Information**: Displays caption, duration, and upload date

### 📱 Gallery Interface
- **Grid Layout**: Clean 3-column grid layout for video thumbnails
- **Hover Effects**: Play button overlay on hover
- **Duration Badge**: Shows video duration on each thumbnail
- **Upload Limits**: Visual indicators for upload limits (20 videos max)

## 🛠️ Setup Instructions

### 1. Install FFmpeg (Required for Thumbnail Generation)

#### Windows:
```bash
# Run the installation script
install-ffmpeg.bat

# Or manually:
# 1. Download from https://www.gyan.dev/ffmpeg/builds/
# 2. Extract to C:\ffmpeg
# 3. Add C:\ffmpeg\bin to PATH
# 4. Update .env file:
FFMPEG_PATH=C:\ffmpeg\bin\ffmpeg.exe
FFPROBE_PATH=C:\ffmpeg\bin\ffprobe.exe
```

#### macOS:
```bash
# Run the installation script
./install-ffmpeg.sh

# Or manually:
brew install ffmpeg
```

#### Linux (Ubuntu/Debian):
```bash
# Run the installation script
./install-ffmpeg.sh

# Or manually:
sudo apt update
sudo apt install ffmpeg
```

### 2. Environment Configuration

Add these lines to your `.env` file:

```env
# FFmpeg Configuration (optional - will auto-detect if not set)
FFMPEG_PATH=/usr/bin/ffmpeg
FFPROBE_PATH=/usr/bin/ffprobe

# Media Library Configuration
MEDIA_DISK=public
MEDIA_PREFIX=
```

### 3. Storage Setup

Make sure your storage is properly configured:

```bash
# Create storage link
php artisan storage:link

# Create thumbnails directory
mkdir -p storage/app/public/thumbnails
```

## 📁 File Structure

```
liveforever/
├── app/
│   ├── Http/Controllers/
│   │   └── VideoController.php          # Video upload/management
│   └── Models/
│       └── User.php                     # Video collection methods
├── resources/js/components/
│   ├── VideoGrid.vue                    # Video gallery display
│   ├── VideoPlayer.vue                  # Video player modal
│   └── VideoUploadModal.vue             # Upload interface
├── storage/app/public/
│   └── thumbnails/                      # Generated thumbnails
└── install-ffmpeg.sh                    # FFmpeg installation script
```

## 🎯 Usage

### Uploading Videos
1. Click the "+" button in the video gallery
2. Drag and drop a video file or click "Select Files"
3. Optionally add a caption
4. Click "Upload" and watch the progress bar
5. The video will appear in the gallery with an auto-generated thumbnail

### Playing Videos
1. Click on any video thumbnail in the gallery
2. The video player will open in a modal
3. Use the video controls to play, pause, seek, etc.
4. Press Escape or click the X to close

### Managing Videos
- **Edit Captions**: Click the edit button to modify video captions
- **Delete Videos**: Remove videos from your collection
- **View Limits**: See how many videos you have (max 20)

## 🔧 Technical Details

### Video Processing
- **Thumbnail Generation**: Uses FFmpeg to extract a frame at 1 second
- **Duration Extraction**: Uses FFprobe to get video duration
- **Format Support**: Handles multiple video formats and codecs
- **Error Handling**: Graceful fallback if FFmpeg is not available

### Frontend Features
- **Progress Tracking**: XMLHttpRequest for real-time upload progress
- **File Validation**: Client-side validation before upload
- **Responsive Design**: Works on all screen sizes
- **Accessibility**: Keyboard navigation and screen reader support

### Backend Features
- **Media Library Integration**: Uses Spatie Media Library for file management
- **Custom Properties**: Stores captions, duration, and thumbnail paths
- **Validation**: Server-side file validation and size limits
- **Error Handling**: Comprehensive error messages and logging

## 🚀 Performance Tips

1. **FFmpeg Installation**: Install FFmpeg for automatic thumbnail generation
2. **Storage Optimization**: Use CDN for video storage in production
3. **Caching**: Implement caching for video metadata
4. **Compression**: Consider video compression for faster uploads

## 🐛 Troubleshooting

### Thumbnails Not Generating
- Check if FFmpeg is installed: `ffmpeg -version`
- Verify FFmpeg paths in `.env` file
- Check storage permissions for thumbnail directory

### Upload Failures
- Check file size limits (500MB max)
- Verify supported video formats
- Check server upload limits in PHP configuration

### Video Player Issues
- Ensure video files are accessible via web server
- Check CORS settings if using external storage
- Verify video format compatibility with browser

## 📝 API Endpoints

```
POST /videos                    # Upload video
GET /users/{id}/videos         # Get user videos
PUT /videos/{id}               # Update video caption
DELETE /videos/{id}            # Delete video
```

## 🔒 Security Features

- **CSRF Protection**: All uploads require CSRF tokens
- **File Validation**: Strict file type and size validation
- **User Authentication**: Videos are tied to authenticated users
- **Path Traversal Protection**: Secure file path handling

## 📊 File Limits

- **Video Size**: Maximum 500MB per video
- **Total Videos**: Maximum 20 videos per user
- **Supported Formats**: MP4, AVI, MOV, WMV, FLV, WebM, MKV, 3GP
- **Thumbnail Size**: 320x240 pixels (auto-generated)

---

For support or questions, please refer to the main application documentation or contact the development team. 