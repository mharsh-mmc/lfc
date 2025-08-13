<?php

namespace App\Http\Controllers;

use App\Services\OldDatabaseImportService;
use App\Services\CompleteFamilyTreeMigrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrationController extends Controller
{
    protected $oldDatabaseImport;
    protected $migrationService;
    
    public function __construct(
        OldDatabaseImportService $oldDatabaseImport,
        CompleteFamilyTreeMigrationService $migrationService
    ) {
        $this->oldDatabaseImport = $oldDatabaseImport;
        $this->migrationService = $migrationService;
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
     * Migrate a specific tree
     */
    public function migrateTree(Request $request, $treeId)
    {
        try {
            $result = $this->migrationService->migrateCompleteFamilyTree($treeId);
            
            return response()->json([
                'success' => true,
                'message' => "Successfully migrated tree {$treeId}",
                'data' => [
                    'tree' => $result['tree'],
                    'nodes_count' => count($result['nodes']),
                    'edges_count' => count($result['edges']),
                    'vueFlowData' => $result['vueFlowData']
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to migrate tree {$treeId}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => "Failed to migrate tree {$treeId}: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Migrate all trees
     */
    public function migrateAllTrees(Request $request)
    {
        try {
            $activeTrees = DB::table('genealogical_tree')
                ->where('flag_active', 1)
                ->get();
                
            if ($activeTrees->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active trees found to migrate'
                ], 404);
            }
            
            $results = [];
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($activeTrees as $tree) {
                try {
                    $result = $this->migrationService->migrateCompleteFamilyTree($tree->id);
                    $results[] = [
                        'old_tree_id' => $tree->id,
                        'new_tree_id' => $result['tree']->id,
                        'status' => 'success',
                        'nodes_count' => count($result['nodes']),
                        'edges_count' => count($result['edges'])
                    ];
                    $successCount++;
                } catch (\Exception $e) {
                    $results[] = [
                        'old_tree_id' => $tree->id,
                        'status' => 'error',
                        'error' => $e->getMessage()
                    ];
                    $errorCount++;
                    Log::error("Migration failed for tree {$tree->id}: " . $e->getMessage());
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Migration completed. Success: {$successCount}, Errors: {$errorCount}",
                'data' => [
                    'total_trees' => $activeTrees->count(),
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'results' => $results
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to migrate all trees: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to migrate all trees: ' . $e->getMessage()
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
            'total_education' => DB::table('education')->count(),
            'total_deceased_profiles' => DB::table('deceased_profiles')->count(),
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