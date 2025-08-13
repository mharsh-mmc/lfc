<?php

namespace App\Support;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class CustomPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        $userId = $media->model->id ?? 'unknown';
        $collectionName = $media->collection_name;
        
        // Map collection names to folder names
        $folderMap = [
            'videos' => 'videos',
            'images' => 'images', 
            'documents' => 'documents',
            'profile-photos' => 'profile-photos',
            'banners' => 'banners'
        ];
        
        $folderName = $folderMap[$collectionName] ?? $collectionName;
        
        return "users/{$userId}/{$folderName}/";
    }

    public function getPathForConversions(Media $media): string
    {
        $userId = $media->model->id ?? 'unknown';
        $collectionName = $media->collection_name;
        
        // Map collection names to folder names
        $folderMap = [
            'videos' => 'videos',
            'images' => 'images',
            'documents' => 'documents',
            'profile-photos' => 'profile-photos',
            'banners' => 'banners'
        ];
        
        $folderName = $folderMap[$collectionName] ?? $collectionName;
        
        return "users/{$userId}/{$folderName}/conversions/";
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        $userId = $media->model->id ?? 'unknown';
        $collectionName = $media->collection_name;
        
        // Map collection names to folder names
        $folderMap = [
            'videos' => 'videos',
            'images' => 'images',
            'documents' => 'documents',
            'profile-photos' => 'profile-photos',
            'banners' => 'banners'
        ];
        
        $folderName = $folderMap[$collectionName] ?? $collectionName;
        
        return "users/{$userId}/{$folderName}/responsive/";
    }
} 