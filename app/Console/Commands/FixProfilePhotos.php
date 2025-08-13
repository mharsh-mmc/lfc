<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class FixProfilePhotos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:profile-photos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and fix profile photos and banners in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking profile photos and banners...');

        $users = User::all();

        foreach ($users as $user) {
            $this->info("User: {$user->name} (ID: {$user->id})");
            
            // Check profile photo
            if ($user->profile_photo_path) {
                $this->info("  Profile photo path: {$user->profile_photo_path}");
                $this->info("  Profile photo URL: " . $user->getProfilePhotoUrl());
                
                // Check if file exists
                if (Storage::disk('public')->exists($user->profile_photo_path)) {
                    $this->info("  ✓ Profile photo file exists");
                } else {
                    $this->warn("  ✗ Profile photo file not found");
                }
            } else {
                $this->info("  No profile photo set");
            }
            
            // Check banner
            if ($user->banner_path) {
                $this->info("  Banner path: {$user->banner_path}");
                $this->info("  Banner URL: " . $user->getBannerUrl());
                
                // Check if file exists
                if (Storage::disk('public')->exists($user->banner_path)) {
                    $this->info("  ✓ Banner file exists");
                } else {
                    $this->warn("  ✗ Banner file not found");
                }
            } else {
                $this->info("  No banner set");
            }
            
            $this->line('');
        }

        $this->info('Check complete!');
    }
} 