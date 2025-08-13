<?php

namespace App\Console\Commands;

use App\Services\OldDatabaseImportService;
use App\Services\CompleteDatabaseMigrationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateFamilyTreesCommand extends Command
{
    protected $signature = 'family-tree:migrate {tree_id?} {--all : Migrate all trees} {--complete : Migrate complete database (ALL tables)} {--import-old : Import old database structure first}';
    protected $description = 'Migrate old family trees to new VueFlow system';
    
    protected $oldDatabaseImport;
    protected $completeMigrationService;
    
    public function __construct(
        OldDatabaseImportService $oldDatabaseImport,
        CompleteDatabaseMigrationService $completeMigrationService
    ) {
        parent::__construct();
        $this->oldDatabaseImport = $oldDatabaseImport;
        $this->completeMigrationService = $completeMigrationService;
    }
    
    public function handle()
    {
        $this->info('🌳 Family Tree Migration Tool');
        $this->info('============================');
        
        // Check if old database is imported
        if (!$this->oldDatabaseImport->isOldDatabaseImported()) {
            if ($this->option('import-old')) {
                $this->info('📥 Importing old database structure...');
                $this->importOldDatabase();
            } else {
                $this->error('❌ Old database not found. Use --import-old to import it first.');
                return 1;
            }
        }
        
        // Show old database stats
        $this->showOldDatabaseStats();
        
        if ($this->option('complete')) {
            // Complete database migration (ALL tables)
            $this->migrateCompleteDatabase();
        } elseif ($treeId = $this->argument('tree_id')) {
            // Migrate specific tree
            $this->migrateSingleTree($treeId);
        } elseif ($this->option('all')) {
            // Migrate all active trees
            $this->migrateAllTrees();
        } else {
            $this->error('❌ Please specify migration type:');
            $this->info('  --complete : Migrate complete database (ALL tables) - RECOMMENDED');
            $this->info('  --all : Migrate all family trees only');
            $this->info('  {tree_id} : Migrate specific tree');
            $this->info('');
            $this->info('Usage examples:');
            $this->info('  php artisan family-tree:migrate --complete --import-old');
            $this->info('  php artisan family-tree:migrate --all');
            $this->info('  php artisan family-tree:migrate 1');
            return 1;
        }
        
        return 0;
    }
    
    /**
     * Import old database structure
     */
    private function importOldDatabase()
    {
        try {
            $this->oldDatabaseImport->importOldDatabaseStructure();
            $this->info('✅ Successfully imported old database structure');
        } catch (\Exception $e) {
            $this->error('❌ Failed to import old database: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Show old database statistics
     */
    private function showOldDatabaseStats()
    {
        $stats = $this->oldDatabaseImport->getOldDatabaseStats();
        
        if ($stats) {
            $this->info('📊 Old Database Statistics:');
            $this->info("   - Total People: {$stats['total_people']}");
            $this->info("   - Active Trees: {$stats['total_trees']}");
            $this->info("   - Tree People: {$stats['total_tree_people']}");
            $this->info("   - Templates: {$stats['total_templates']}");
            $this->info("   - Cities: {$stats['total_cities']}");
            $this->info('');
        }
    }
    
    /**
     * Complete database migration (ALL tables)
     */
    private function migrateCompleteDatabase()
    {
        try {
            $this->info('🚀 Starting complete database migration...');
            $this->info('This will migrate ALL tables: users, family trees, education, media, deceased profiles, etc.');
            
            if (!$this->confirm('Are you sure you want to migrate the complete database?')) {
                $this->info('Migration cancelled.');
                return;
            }
            
            $result = $this->completeMigrationService->migrateCompleteDatabase();
            
            $this->info('🎉 Complete database migration successful!');
            $this->showCompleteMigrationResults($result);
            
        } catch (\Exception $e) {
            $this->error("❌ Complete database migration failed: " . $e->getMessage());
            Log::error("Complete database migration failed: " . $e->getMessage());
        }
    }
    
    /**
     * Show complete migration results
     */
    private function showCompleteMigrationResults($result)
    {
        $summary = $result['summary'];
        
        $this->info('📊 Migration Summary:');
        $this->info("   - Users Migrated: {$summary['total_users_migrated']}");
        $this->info("   - Family Trees: {$summary['total_family_trees_migrated']}");
        $this->info("   - Education Records: {$summary['total_education_records']}");
        $this->info("   - Deceased Profiles: {$summary['total_deceased_profiles']}");
        $this->info("   - Media Files: {$summary['total_media_files']}");
        $this->info("   - Cities: {$summary['total_cities']}");
        $this->info("   - Additional Tables: {$summary['total_additional_tables']}");
        $this->info("   - Migration Date: {$summary['migration_date']}");
        $this->info("   - Note: {$summary['note']}");
        
        $this->info('');
        $this->info('✅ All data has been successfully migrated to your new database!');
        $this->info('🌐 Your VueFlow family trees are now ready to use.');
        $this->info('📚 All education, media, and metadata tables are preserved.');
    }
    
    /**
     * Migrate a single tree
     */
    private function migrateSingleTree($treeId)
    {
        try {
            $this->info("🔄 Migrating tree ID: {$treeId}");
            
            // For single tree, we'll do complete migration
            $this->warn('⚠️  Single tree migration is deprecated. Use --complete for full migration.');
            $this->info('Starting complete migration...');
            
            $this->migrateCompleteDatabase();
            
        } catch (\Exception $e) {
            $this->error("❌ Failed to migrate tree {$treeId}: " . $e->getMessage());
            Log::error("Migration failed for tree {$treeId}: " . $e->getMessage());
        }
    }
    
    /**
     * Migrate all trees
     */
    private function migrateAllTrees()
    {
        try {
            $this->info("🔄 Migrating all family trees...");
            
            // For all trees, we'll do complete migration
            $this->warn('⚠️  All trees migration is deprecated. Use --complete for full migration.');
            $this->info('Starting complete migration...');
            
            $this->migrateCompleteDatabase();
            
        } catch (\Exception $e) {
            $this->error("❌ Failed to migrate all trees: " . $e->getMessage());
            Log::error("Failed to migrate all trees: " . $e->getMessage());
        }
    }
}