<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing video data:\n";

$user = App\Models\User::first();
if ($user) {
    $videos = $user->getVideos();
    echo "Videos count: " . count($videos) . "\n";
    
    if (count($videos) > 0) {
        echo "First video data:\n";
        print_r($videos[0]);
        
        echo "\nChecking thumbnail path:\n";
        $thumbnail = $videos[0]['thumbnail'];
        echo "Thumbnail URL: " . $thumbnail . "\n";
        
        // Check if the file exists
        if (strpos($thumbnail, '/storage/') === 0) {
            $filePath = public_path('storage/' . str_replace('/storage/', '', $thumbnail));
            echo "File path: " . $filePath . "\n";
            echo "File exists: " . (file_exists($filePath) ? 'Yes' : 'No') . "\n";
        } elseif (strpos($thumbnail, '/default-') === 0) {
            $filePath = public_path($thumbnail);
            echo "Default file path: " . $filePath . "\n";
            echo "File exists: " . (file_exists($filePath) ? 'Yes' : 'No') . "\n";
        }
    } else {
        echo "No videos found\n";
    }
} else {
    echo "No user found\n";
} 