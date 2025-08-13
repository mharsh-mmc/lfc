<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class TestMediaSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:media-system';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the media system to ensure profile photos and banners are working';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Media System...');
        
        $user = User::first();
        
        if (!$user) {
            $this->error('No users found in database');
            return 1;
        }
        
        $this->info("User found: {$user->name}");
        $this->info("Profile photos count: " . $user->getMedia('profile-photos')->count());
        $this->info("Banners count: " . $user->getMedia('banners')->count());
        $this->info("Profile photo URL: " . ($user->getProfilePhotoUrl() ?? 'NULL'));
        $this->info("Banner URL: " . ($user->getBannerUrl() ?? 'NULL'));
        
        // Test the regular methods
        $this->info("\nTesting Regular Methods:");
        $this->info("getProfilePhotoUrl(): " . $user->getProfilePhotoUrl());
        $this->info("getBannerUrl(): " . $user->getBannerUrl());
        
        // Test media collections
        $this->info("\nTesting Media Collections:");
        $profilePhotos = $user->getMedia('profile-photos');
        $banners = $user->getMedia('banners');
        
        if ($profilePhotos->count() > 0) {
            $this->info("Latest profile photo: " . $profilePhotos->first()->getUrl('profile'));
        }
        
        if ($banners->count() > 0) {
            $this->info("Latest banner: " . $banners->first()->getUrl('banner'));
        }
        
        $this->info("\nMedia system test completed!");
        return 0;
    }
} 