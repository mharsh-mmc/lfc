<?php

namespace App\Console\Commands;

use App\Services\OldDatabaseImportService;
use App\Services\CompleteFamilyTreeMigrationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateFamilyTreesCommand extends Command
{
    protected $signature = 'family-tree:migrate {tree_id?} {--all : Migrate all trees} {--import-old : Import old database structure first}';
    protected $description = 'Migrate old family trees to new VueFlow system';
    
    protected $oldDatabaseImport;
    protected $migrationService;
    
    public function __construct(
        OldDatabaseImportService $oldDatabaseImport,
        CompleteFamilyTreeMigrationService $migrationService
    ) {
        parent::__construct();
        $this->oldDatabaseImport = $oldDatabaseImport;
        $this->migrationService = $migrationService;
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
        
        if ($treeId = $this->argument('tree_id')) {
            // Migrate specific tree
            $this->migrateSingleTree($treeId);
        } elseif ($this->option('all')) {
            // Migrate all active trees
            $this->migrateAllTrees();
        } else {
            $this->error('❌ Please specify a tree ID or use --all to migrate all trees.');
            $this->info('Usage examples:');
            $this->info('  php artisan family-tree:migrate 1');
            $this->info('  php artisan family-tree:migrate --all');
            $this->info('  php artisan family-tree:migrate --import-old --all');
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
     * Migrate a single tree
     */
    private function migrateSingleTree($treeId)
    {
        try {
            $this->info("🔄 Migrating tree ID: {$treeId}");
            
            $result = $this->migrationService->migrateCompleteFamilyTree($treeId);
            
            $this->info('✅ Successfully migrated tree!');
            $this->info("   - Tree ID: {$result['tree']->id}");
            $this->info("   - Tree Name: {$result['tree']->name}");
            $this->info("   - Nodes created: " . count($result['nodes']));
            $this->info("   - Edges created: " . count($result['edges']));
            
            // Show VueFlow data preview
            $this->showVueFlowPreview($result['vueFlowData']);
            
        } catch (\Exception $e) {
            $this->error("❌ Failed to migrate tree {$treeId}: " . $e->getMessage());
            Log::error("Migration failed for tree {$treeId}: " . $e->getMessage());
        }
    }
    
    /**
     * Migrate all active trees
     */
    private function migrateAllTrees()
    {
        $activeTrees = DB::table('genealogical_tree')
            ->where('flag_active', 1)
            ->get();
            
        $this->info("🔄 Found {$activeTrees->count()} active trees to migrate");
        
        if ($activeTrees->isEmpty()) {
            $this->warn('⚠️  No active trees found to migrate');
            return;
        }
        
        $bar = $this->output->createProgressBar($activeTrees->count());
        $bar->start();
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($activeTrees as $tree) {
            try {
                $this->migrationService->migrateCompleteFamilyTree($tree->id);
                $successCount++;
                $bar->advance();
            } catch (\Exception $e) {
                $errorCount++;
                $this->error("\n❌ Failed to migrate tree {$tree->id}: " . $e->getMessage());
                Log::error("Migration failed for tree {$tree->id}: " . $e->getMessage());
            }
        }
        
        $bar->finish();
        $this->info("\n");
        $this->info("🎉 Migration completed!");
        $this->info("   - Successfully migrated: {$successCount} trees");
        $this->info("   - Failed migrations: {$errorCount} trees");
        
        if ($errorCount > 0) {
            $this->warn("⚠️  Check logs for details on failed migrations");
        }
    }
    
    /**
     * Show VueFlow data preview
     */
    private function showVueFlowPreview($vueFlowData)
    {
        $this->info('📋 VueFlow Data Preview:');
        $this->info("   - Nodes: " . count($vueFlowData['nodes']));
        $this->info("   - Edges: " . count($vueFlowData['edges']));
        
        if (!empty($vueFlowData['nodes'])) {
            $this->info('   - Sample Node: ' . $vueFlowData['nodes'][0]['data']['label']);
        }
        
        if (!empty($vueFlowData['edges'])) {
            $this->info('   - Sample Edge: ' . $vueFlowData['edges'][0]['data']['relationship_type']);
        }
    }
}