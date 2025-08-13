<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CleanupMediaSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:media-system {--force : Force cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up all old media files, folders, and reset database for new media system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will delete ALL media files, folders, and reset the database. Are you sure?')) {
                $this->info('Cleanup cancelled.');
                return;
            }
        }

        $this->info('Starting comprehensive media system cleanup...');

        // Step 1: Clean up old media folders and files
        $this->cleanupOldMediaFolders();

        // Step 2: Clean up Spatie Media Library files
        $this->cleanupSpatieMediaFiles();

        // Step 3: Reset database
        $this->resetDatabase();

        // Step 4: Clear caches
        $this->clearCaches();

        $this->info('✅ Media system cleanup completed successfully!');
        $this->info('Database has been reset and all old media files have been removed.');
    }

    /**
     * Clean up old media folders and files
     */
    private function cleanupOldMediaFolders()
    {
        $this->info('🧹 Cleaning up old media folders...');

        $foldersToDelete = [
            storage_path('app/public/banners'),
            storage_path('app/public/profile-photos'),
            storage_path('app/public/users'),
            storage_path('app/public/thumbnails'),
            storage_path('app/public/videos'),
            storage_path('app/public/images'),
            storage_path('app/public/documents'),
        ];

        foreach ($foldersToDelete as $folder) {
            if (File::exists($folder)) {
                File::deleteDirectory($folder);
                $this->info("   Deleted: " . basename($folder));
            }
        }

        // Clean up any remaining files in public storage
        $publicStoragePath = storage_path('app/public');
        $this->cleanupDirectory($publicStoragePath, ['media', '.gitignore']);

        $this->info('✅ Old media folders cleaned up.');
    }

    /**
     * Clean up Spatie Media Library files
     */
    private function cleanupSpatieMediaFiles()
    {
        $this->info('🧹 Cleaning up Spatie Media Library files...');

        // Get all media files from the database
        $mediaFiles = DB::table('media')->get();

        foreach ($mediaFiles as $media) {
            try {
                // Delete the actual file
                $filePath = storage_path('app/public/' . $media->file_name);
                if (File::exists($filePath)) {
                    File::delete($filePath);
                }

                // Delete conversions if they exist
                $conversionsPath = storage_path('app/public/' . pathinfo($media->file_name, PATHINFO_FILENAME));
                if (File::exists($conversionsPath)) {
                    File::deleteDirectory($conversionsPath);
                }
            } catch (\Exception $e) {
                $this->warn("   Could not delete file: {$media->file_name}");
            }
        }

        $this->info('✅ Spatie Media Library files cleaned up.');
    }

    /**
     * Reset database
     */
    private function resetDatabase()
    {
        $this->info('🗄️ Resetting database...');

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // Clear all tables
        $tables = [
            'users',
            'media',
            'personal_access_tokens',
            'password_reset_tokens',
            'sessions',
            'failed_jobs',
            'cache',
            'cache_locks',
            'jobs',
            'migrations',
        ];

        foreach ($tables as $table) {
            try {
                DB::table($table)->truncate();
                $this->info("   Cleared table: {$table}");
            } catch (\Exception $e) {
                $this->warn("   Could not clear table: {$table}");
            }
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        $this->info('✅ Database reset completed.');
    }

    /**
     * Clear all caches
     */
    private function clearCaches()
    {
        $this->info('🧹 Clearing caches...');

        // Clear application cache
        $this->call('cache:clear');
        
        // Clear config cache
        $this->call('config:clear');
        
        // Clear route cache
        $this->call('route:clear');
        
        // Clear view cache
        $this->call('view:clear');

        $this->info('✅ Caches cleared.');
    }

    /**
     * Clean up directory contents except for specified files
     */
    private function cleanupDirectory($directory, $excludeFiles = [])
    {
        if (!File::exists($directory)) {
            return;
        }

        $files = File::files($directory);
        $directories = File::directories($directory);

        // Delete files except excluded ones
        foreach ($files as $file) {
            $fileName = basename($file);
            if (!in_array($fileName, $excludeFiles)) {
                File::delete($file);
            }
        }

        // Delete directories
        foreach ($directories as $dir) {
            File::deleteDirectory($dir);
        }
    }
} 