@echo off
echo Installing FFmpeg for video thumbnail generation...
echo.

echo Windows detected. Please install FFmpeg manually:
echo.
echo 1. Download FFmpeg from https://ffmpeg.org/download.html
echo    or visit https://www.gyan.dev/ffmpeg/builds/ for Windows builds
echo.
echo 2. Extract the downloaded zip file to C:\ffmpeg
echo.
echo 3. Add C:\ffmpeg\bin to your system PATH:
echo    - Open System Properties ^> Advanced ^> Environment Variables
echo    - Edit the PATH variable and add C:\ffmpeg\bin
echo.
echo 4. Update your .env file with:
echo    FFMPEG_PATH=C:\ffmpeg\bin\ffmpeg.exe
echo    FFPROBE_PATH=C:\ffmpeg\bin\ffprobe.exe
echo.
echo 5. Test the installation by running:
echo    ffmpeg -version
echo    ffprobe -version
echo.
echo After installation, your Laravel application can generate video thumbnails!
echo.
pause 