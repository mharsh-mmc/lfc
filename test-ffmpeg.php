<?php

echo "=== FFmpeg Installation Test ===\n\n";

// Test 1: Check if FFmpeg is in PATH
echo "1. Testing FFmpeg in PATH:\n";
$ffmpegVersion = shell_exec('ffmpeg -version 2>&1');
if ($ffmpegVersion && strpos($ffmpegVersion, 'ffmpeg version') !== false) {
    echo "✅ FFmpeg found in PATH\n";
    echo "Version: " . substr($ffmpegVersion, 0, 50) . "...\n\n";
} else {
    echo "❌ FFmpeg not found in PATH\n\n";
}

// Test 2: Check if FFmpeg is in specific Windows path
echo "2. Testing FFmpeg in C:\\ffmpeg\\bin:\n";
$ffmpegPath = 'C:\ffmpeg\bin\ffmpeg.exe';
if (file_exists($ffmpegPath)) {
    echo "✅ FFmpeg executable found at: $ffmpegPath\n";
    $version = shell_exec('"' . $ffmpegPath . '" -version 2>&1');
    if ($version && strpos($version, 'ffmpeg version') !== false) {
        echo "✅ FFmpeg is working\n";
        echo "Version: " . substr($version, 0, 50) . "...\n\n";
    } else {
        echo "❌ FFmpeg found but not working\n\n";
    }
} else {
    echo "❌ FFmpeg not found at: $ffmpegPath\n\n";
}

// Test 3: Check FFprobe
echo "3. Testing FFprobe:\n";
$ffprobePath = 'C:\ffmpeg\bin\ffprobe.exe';
if (file_exists($ffprobePath)) {
    echo "✅ FFprobe executable found at: $ffprobePath\n";
    $version = shell_exec('"' . $ffprobePath . '" -version 2>&1');
    if ($version && strpos($version, 'ffprobe version') !== false) {
        echo "✅ FFprobe is working\n";
        echo "Version: " . substr($version, 0, 50) . "...\n\n";
    } else {
        echo "❌ FFprobe found but not working\n\n";
    }
} else {
    echo "❌ FFprobe not found at: $ffprobePath\n\n";
}

// Test 4: Environment variables
echo "4. Environment Variables:\n";
echo "FFMPEG_PATH: " . (getenv('FFMPEG_PATH') ?: 'Not set') . "\n";
echo "FFPROBE_PATH: " . (getenv('FFPROBE_PATH') ?: 'Not set') . "\n\n";

// Test 5: Laravel configuration
echo "5. Laravel Configuration:\n";
echo "config('media-library.ffmpeg_path'): " . (config('media-library.ffmpeg_path') ?: 'Not set') . "\n";
echo "config('media-library.ffprobe_path'): " . (config('media-library.ffprobe_path') ?: 'Not set') . "\n\n";

echo "=== Installation Instructions ===\n";
echo "If FFmpeg is not working, follow these steps:\n";
echo "1. Download FFmpeg from: https://www.gyan.dev/ffmpeg/builds/\n";
echo "2. Extract to C:\\ffmpeg\n";
echo "3. Add C:\\ffmpeg\\bin to your system PATH\n";
echo "4. Restart your terminal/IDE\n";
echo "5. Run this test again\n\n";

echo "=== Alternative Solutions ===\n";
echo "If you can't install FFmpeg, you can:\n";
echo "1. Use a default thumbnail image\n";
echo "2. Generate thumbnails on the frontend using HTML5 video\n";
echo "3. Use a cloud service for video processing\n";
