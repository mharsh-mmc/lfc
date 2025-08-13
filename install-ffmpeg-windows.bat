@echo off
echo ========================================
echo FFmpeg Installation Helper for Windows
echo ========================================
echo.

echo Current FFmpeg Status:
where ffmpeg >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ FFmpeg found in PATH
    ffmpeg -version | findstr "ffmpeg version"
) else (
    echo ❌ FFmpeg not found in PATH
)

echo.
echo Checking for FFmpeg in common locations:
if exist "C:\ffmpeg\bin\ffmpeg.exe" (
    echo ✅ FFmpeg found at C:\ffmpeg\bin\ffmpeg.exe
) else (
    echo ❌ FFmpeg not found at C:\ffmpeg\bin\ffmpeg.exe
)

if exist "C:\ffmpeg-7.1.1\ffmpeg-7.1.1\ffmpeg.exe" (
    echo ✅ FFmpeg found at C:\ffmpeg-7.1.1\ffmpeg-7.1.1\ffmpeg.exe
) else (
    echo ❌ FFmpeg not found at C:\ffmpeg-7.1.1\ffmpeg-7.1.1\ffmpeg.exe
)

echo.
echo ========================================
echo INSTALLATION INSTRUCTIONS:
echo ========================================
echo.
echo 1. Download FFmpeg from: https://www.gyan.dev/ffmpeg/builds/
echo    - Choose: ffmpeg-release-essentials.zip
echo    - NOT the source code version
echo.
echo 2. Extract to C:\ffmpeg
echo    - Create folder: C:\ffmpeg
echo    - Extract contents to: C:\ffmpeg\bin\
echo.
echo 3. Add to PATH:
echo    - Press Win + R, type: sysdm.cpl
echo    - Advanced tab → Environment Variables
echo    - System Variables → Path → Edit
echo    - Add: C:\ffmpeg\bin
echo.
echo 4. Restart your terminal/IDE
echo.
echo 5. Test with: ffmpeg -version
echo.
pause 