<?php

namespace App\Http\Controllers;

use App\Services\OldDatabaseImportService;
use App\Services\FocusedMigrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrationController extends Controller
{
    protected $oldDatabaseImport;
    protected $focusedMigrationService;
    
    public function __construct(
        OldDatabaseImportService $oldDatabaseImport,
        FocusedMigrationService $focusedMigrationService
    ) {
        $this->oldDatabaseImport = $oldDatabaseImport;
        $this->focusedMigrationService = $focusedMigrationService;
    }
    
    /**
     * Show migration dashboard
     */
    public function index()
    {
        $oldDatabaseStats = $this->oldDatabaseImport->getOldDatabaseStats();
        $newDatabaseStats = $this->getNewDatabaseStats();
        
        return view('migration.dashboard', compact('oldDatabaseStats', 'newDatabaseStats'));
    }
    
    /**
     * Import old database structure
     */
    public function importOldDatabase(Request $request)
    {
        try {
            $this->oldDatabaseImport->importOldDatabaseStructure();
            
            return response()->json([
                'success' => true,
                'message' => 'Successfully imported old database structure',
                'stats' => $this->oldDatabaseImport->getOldDatabaseStats()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to import old database: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to import old database: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Migrate core data (Users + Family Trees only)
     */
    public function migrateCoreData(Request $request)
    {
        try {
            $result = $this->focusedMigrationService->migrateCoreData();
            
            return response()->json([
                'success' => true,
                'message' => 'Successfully migrated core data (Users + Family Trees)',
                'data' => [
                    'users_migrated' => count($result['results']['users']),
                    'family_trees_migrated' => count($result['results']['family_trees']),
                    'summary' => $result['summary']
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to migrate core data: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to migrate core data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Migrate a specific tree
     */
    public function migrateTree(Request $request, $treeId)
    {
        try {
            // For single tree, we'll do core migration
            $result = $this->focusedMigrationService->migrateCoreData();
            
            return response()->json([
                'success' => true,
                'message' => "Successfully migrated core data including tree {$treeId}",
                'data' => [
                    'users_migrated' => count($result['results']['users']),
                    'family_trees_migrated' => count($result['results']['family_trees']),
                    'summary' => $result['summary']
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to migrate core data: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => "Failed to migrate core data: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Migrate all trees
     */
    public function migrateAllTrees(Request $request)
    {
        try {
            // For all trees, we'll do core migration
            $result = $this->focusedMigrationService->migrateCoreData();
            
            return response()->json([
                'success' => true,
                'message' => "Successfully migrated core data including all family trees",
                'data' => [
                    'users_migrated' => count($result['results']['users']),
                    'family_trees_migrated' => count($result['results']['family_trees']),
                    'summary' => $result['summary']
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to migrate core data: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to migrate core data: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get migration status
     */
    public function getStatus()
    {
        $oldDatabaseStats = $this->oldDatabaseImport->getOldDatabaseStats();
        $newDatabaseStats = $this->getNewDatabaseStats();
        
        return response()->json([
            'old_database' => [
                'imported' => $this->oldDatabaseImport->isOldDatabaseImported(),
                'stats' => $oldDatabaseStats
            ],
            'new_database' => [
                'stats' => $newDatabaseStats
            ],
            'migration_ready' => $this->oldDatabaseImport->isOldDatabaseImported()
        ]);
    }
    
    /**
     * Get new database statistics
     */
    private function getNewDatabaseStats()
    {
        return [
            'total_users' => DB::table('users')->count(),
            'total_family_trees' => DB::table('family_tree_layouts')->count(),
            'total_tree_nodes' => DB::table('family_tree_nodes')->count(),
            'total_tree_edges' => DB::table('family_tree_edges')->count(),
        ];
    }
    
    /**
     * Get available trees for migration
     */
    public function getAvailableTrees()
    {
        if (!$this->oldDatabaseImport->isOldDatabaseImported()) {
            return response()->json([
                'success' => false,
                'message' => 'Old database not imported'
            ], 404);
        }
        
        $trees = DB::table('genealogical_tree')
            ->where('flag_active', 1)
            ->join('genealogical_tree_template', 'genealogical_tree.gttid', '=', 'genealogical_tree_template.id')
            ->select(
                'genealogical_tree.id',
                'genealogical_tree.pid',
                'genealogical_tree.flag_active',
                'genealogical_tree_template.title',
                'genealogical_tree_template.description'
            )
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $trees
        ]);
    }
}