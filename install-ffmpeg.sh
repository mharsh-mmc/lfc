#!/bin/bash

# FFmpeg Installation Script for Laravel Media Library
# This script installs FFmpeg and FFprobe for video thumbnail generation

echo "Installing FFmpeg for video thumbnail generation..."

# Check if running on Windows
if [[ "$OSTYPE" == "msys" || "$OSTYPE" == "cygwin" ]]; then
    echo "Windows detected. Please install FFmpeg manually:"
    echo "1. Download FFmpeg from https://ffmpeg.org/download.html"
    echo "2. Extract to C:\\ffmpeg"
    echo "3. Add C:\\ffmpeg\\bin to your PATH"
    echo "4. Update your .env file with:"
    echo "   FFMPEG_PATH=C:\\ffmpeg\\bin\\ffmpeg.exe"
    echo "   FFPROBE_PATH=C:\\ffmpeg\\bin\\ffprobe.exe"
    exit 1
fi

# Check if running on macOS
if [[ "$OSTYPE" == "darwin"* ]]; then
    echo "macOS detected. Installing FFmpeg using Homebrew..."
    if ! command -v brew &> /dev/null; then
        echo "Homebrew not found. Installing Homebrew first..."
        /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
    fi
    brew install ffmpeg
    echo "FFmpeg installed successfully on macOS!"
    echo "FFmpeg path: $(which ffmpeg)"
    echo "FFprobe path: $(which ffprobe)"
fi

# Check if running on Linux
if [[ "$OSTYPE" == "linux-gnu"* ]]; then
    echo "Linux detected. Installing FFmpeg..."
    
    # Check if apt is available (Ubuntu/Debian)
    if command -v apt &> /dev/null; then
        echo "Installing FFmpeg using apt..."
        sudo apt update
        sudo apt install -y ffmpeg
    # Check if yum is available (CentOS/RHEL)
    elif command -v yum &> /dev/null; then
        echo "Installing FFmpeg using yum..."
        sudo yum install -y epel-release
        sudo yum install -y ffmpeg
    # Check if dnf is available (Fedora)
    elif command -v dnf &> /dev/null; then
        echo "Installing FFmpeg using dnf..."
        sudo dnf install -y ffmpeg
    else
        echo "Package manager not found. Please install FFmpeg manually."
        echo "Visit https://ffmpeg.org/download.html for instructions."
        exit 1
    fi
    
    echo "FFmpeg installed successfully on Linux!"
    echo "FFmpeg path: $(which ffmpeg)"
    echo "FFprobe path: $(which ffprobe)"
fi

# Test FFmpeg installation
if command -v ffmpeg &> /dev/null; then
    echo "✅ FFmpeg is installed and working!"
    ffmpeg -version | head -n 1
else
    echo "❌ FFmpeg installation failed!"
    exit 1
fi

# Test FFprobe installation
if command -v ffprobe &> /dev/null; then
    echo "✅ FFprobe is installed and working!"
    ffprobe -version | head -n 1
else
    echo "❌ FFprobe installation failed!"
    exit 1
fi

echo ""
echo "🎉 FFmpeg installation completed successfully!"
echo ""
echo "Your Laravel application can now generate video thumbnails."
echo "The following features are now available:"
echo "- Automatic video thumbnail generation"
echo "- Video duration extraction"
echo "- Support for multiple video formats"
echo ""
echo "Note: If you're using a different FFmpeg path, update your .env file:"
echo "FFMPEG_PATH=/path/to/ffmpeg"
echo "FFPROBE_PATH=/path/to/ffprobe" 