<?php

echo "=== GD Extension Test ===\n\n";

// Check if GD is loaded
if (extension_loaded('gd')) {
    echo "✅ GD extension is loaded\n";
    echo "GD Version: " . gd_info()['GD Version'] . "\n";
    echo "Supported formats: " . implode(', ', array_keys(array_filter(gd_info(), function($v) { return $v; }))) . "\n\n";
} else {
    echo "❌ GD extension is not loaded\n\n";
    exit(1);
}

// Test image creation
echo "Testing image creation:\n";
try {
    $width = 320;
    $height = 240;
    
    $image = imagecreatetruecolor($width, $height);
    if (!$image) {
        throw new Exception('Failed to create image resource');
    }
    echo "✅ Image resource created successfully\n";
    
    // Define colors
    $bgColor = imagecolorallocate($image, 52, 73, 94);
    $textColor = imagecolorallocate($image, 255, 255, 255);
    
    // Fill background
    imagefill($image, 0, 0, $bgColor);
    echo "✅ Background filled successfully\n";
    
    // Add text
    imagestring($image, 4, 120, 110, 'TEST', $textColor);
    echo "✅ Text added successfully\n";
    
    // Save as JPEG
    $testPath = __DIR__ . '/test-thumbnail.jpg';
    $success = imagejpeg($image, $testPath, 95);
    imagedestroy($image);
    
    if ($success) {
        echo "✅ JPEG saved successfully\n";
        echo "File size: " . filesize($testPath) . " bytes\n";
        
        // Check if it's a valid JPEG
        $fileContent = file_get_contents($testPath);
        if (strlen($fileContent) >= 2 && substr($fileContent, 0, 2) === "\xFF\xD8") {
            echo "✅ File is a valid JPEG\n";
        } else {
            echo "❌ File is not a valid JPEG\n";
        }
        
        // Clean up
        unlink($testPath);
        echo "✅ Test file cleaned up\n";
    } else {
        echo "❌ Failed to save JPEG\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
