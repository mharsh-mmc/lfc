<?php

// Create a default video thumbnail image
$width = 320;
$height = 240;

// Create image resource
$image = imagecreatetruecolor($width, $height);

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

// Save as JPEG
$thumbnailPath = __DIR__ . '/public/default-video-thumbnail.jpg';
imagejpeg($image, $thumbnailPath, 95);
imagedestroy($image);

echo "Default thumbnail created at: $thumbnailPath\n";
echo "File size: " . filesize($thumbnailPath) . " bytes\n";
echo "Image dimensions: " . getimagesize($thumbnailPath)[0] . "x" . getimagesize($thumbnailPath)[1] . "\n";
