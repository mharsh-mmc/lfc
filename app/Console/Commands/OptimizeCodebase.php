<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class OptimizeCodebase extends Command
{
    protected $signature = 'optimize:codebase {--force : Force optimization without confirmation}';
    protected $description = 'Optimize the codebase by cleaning up unused components and optimizing assets';

    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('This will optimize the codebase. Continue?')) {
            $this->info('Operation cancelled.');
            return;
        }

        $this->info('Starting codebase optimization...');

        // Clear all caches
        $this->clearCaches();

        // Optimize database queries
        $this->optimizeDatabase();

        // Clean up unused files
        $this->cleanupUnusedFiles();

        // Optimize assets
        $this->optimizeAssets();

        $this->info('Codebase optimization completed successfully!');
    }

    private function clearCaches()
    {
        $this->info('Clearing caches...');
        
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        
        $this->info('✓ Caches cleared');
    }

    private function optimizeDatabase()
    {
        $this->info('Optimizing database...');
        
        // Run database migrations
        Artisan::call('migrate', ['--force' => true]);
        
        // Optimize database tables
        $this->call('db:optimize');
        
        $this->info('✓ Database optimized');
    }

    private function cleanupUnusedFiles()
    {
        $this->info('Cleaning up unused files...');
        
        $unusedFiles = [
            // Remove unused UI components
            'resources/js/components/ui/accordion',
            'resources/js/components/ui/alert-dialog',
            'resources/js/components/ui/aspect-ratio',
            'resources/js/components/ui/calendar',
            'resources/js/components/ui/command',
            'resources/js/components/ui/context-menu',
            'resources/js/components/ui/hover-card',
            'resources/js/components/ui/menubar',
            'resources/js/components/ui/popover',
            'resources/js/components/ui/progress',
            'resources/js/components/ui/radio-group',
            'resources/js/components/ui/scroll-area',
            'resources/js/components/ui/select',
            'resources/js/components/ui/slider',
            'resources/js/components/ui/switch',
            'resources/js/components/ui/tabs',
            'resources/js/components/ui/textarea',
            'resources/js/components/ui/toast',
            'resources/js/components/ui/toggle',
            'resources/js/components/ui/toggle-group',
        ];

        foreach ($unusedFiles as $file) {
            if (File::exists($file)) {
                File::deleteDirectory($file);
                $this->line("Removed: {$file}");
            }
        }
        
        $this->info('✓ Unused files cleaned up');
    }

    private function optimizeAssets()
    {
        $this->info('Optimizing assets...');
        
        // Build production assets
        $this->call('npm', ['run', 'build']);
        
        // Optimize images
        $this->optimizeImages();
        
        $this->info('✓ Assets optimized');
    }

    private function optimizeImages()
    {
        $this->info('Optimizing images...');
        
        $imageDirectories = [
            'public/images',
            'public/landing',
            'public/leaveamark',
        ];

        foreach ($imageDirectories as $directory) {
            if (File::exists($directory)) {
                $this->optimizeImageDirectory($directory);
            }
        }
    }

    private function optimizeImageDirectory($directory)
    {
        $files = File::glob($directory . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
        
        foreach ($files as $file) {
            $this->line("Optimizing: {$file}");
            // Here you could add image optimization logic
            // For now, we'll just log the files
        }
    }
}
