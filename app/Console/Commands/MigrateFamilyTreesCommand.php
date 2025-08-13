<?php

namespace App\Console\Commands;

use App\Services\OldDatabaseImportService;
use App\Services\FocusedMigrationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateFamilyTreesCommand extends Command
{
    protected $signature = 'family-tree:migrate {tree_id?} {--all : Migrate all trees} {--core : Migrate core data only (Users + Family Trees)} {--import-old : Import old database structure first}';
    protected $description = 'Migrate old family trees to new VueFlow system';
    
    protected $oldDatabaseImport;
    protected $focusedMigrationService;
    
    public function __construct(
        OldDatabaseImportService $oldDatabaseImport,
        FocusedMigrationService $focusedMigrationService
    ) {
        parent::__construct();
        $this->oldDatabaseImport = $oldDatabaseImport;
        $this->focusedMigrationService = $focusedMigrationService;
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
        
        if ($this->option('core')) {
            // Core data migration (Users + Family Trees only)
            $this->migrateCoreData();
        } elseif ($treeId = $this->argument('tree_id')) {
            // Migrate specific tree
            $this->migrateSingleTree($treeId);
        } elseif ($this->option('all')) {
            // Migrate all active trees
            $this->migrateAllTrees();
        } else {
            $this->error('❌ Please specify migration type:');
            $this->info('  --core : Migrate core data only (Users + Family Trees) - RECOMMENDED');
            $this->info('  --all : Migrate all family trees only');
            $this->info('  {tree_id} : Migrate specific tree');
            $this->info('');
            $this->info('Usage examples:');
            $this->info('  php artisan family-tree:migrate --core --import-old');
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
     * Core data migration (Users + Family Trees only)
     */
    private function migrateCoreData()
    {
        try {
            $this->info('🎯 Starting core data migration...');
            $this->info('This will migrate ONLY:');
            $this->info('  ✅ Users (anagrafica → users)');
            $this->info('  ✅ Family Trees (with nodes and edges)');
            $this->info('  ❌ NO extra metadata tables');
            $this->info('  ❌ NO education/media/deceased profiles');
            
            if (!$this->confirm('Proceed with core data migration?')) {
                $this->info('Migration cancelled.');
                return;
            }
            
            $result = $this->focusedMigrationService->migrateCoreData();
            
            $this->info('🎉 Core data migration successful!');
            $this->showCoreMigrationResults($result);
            
        } catch (\Exception $e) {
            $this->error("❌ Core data migration failed: " . $e->getMessage());
            Log::error("Core data migration failed: " . $e->getMessage());
        }
    }
    
    /**
     * Show core migration results
     */
    private function showCoreMigrationResults($result)
    {
        $summary = $result['summary'];
        
        $this->info('📊 Migration Summary:');
        $this->info("   - Users Migrated: {$summary['total_users_migrated']}");
        $this->info("   - Family Trees: {$summary['total_family_trees_migrated']}");
        $this->info("   - Migration Date: {$summary['migration_date']}");
        $this->info("   - Note: {$summary['note']}");
        
        $this->info('');
        $this->info('✅ Core data has been successfully migrated!');
        $this->info('🌐 Your VueFlow family trees are now ready to use.');
        $this->info('👥 All users are now available in your new system.');
    }
    
    /**
     * Migrate a single tree
     */
    private function migrateSingleTree($treeId)
    {
        try {
            $this->info("🔄 Migrating tree ID: {$treeId}");
            
            // For single tree migration, we'll use the focused service
            $this->warn('⚠️  Single tree migration is deprecated. Use --core for full migration.');
            $this->info('Starting core migration...');
            
            $this->migrateCoreData();
            
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
            
            // For all trees migration, we'll use the focused service
            $this->warn('⚠️  All trees migration is deprecated. Use --core for full migration.');
            $this->info('Starting core migration...');
            
            $this->migrateCoreData();
            
        } catch (\Exception $e) {
            $this->error("❌ Failed to migrate all trees: " . $e->getMessage());
            Log::error("Failed to migrate all trees: " . $e->getMessage());
        }
    }
}