<?php

namespace App\Http\Controllers;

use App\Services\OldDatabaseImportService;
use App\Services\CompleteDatabaseMigrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrationController extends Controller
{
    protected $oldDatabaseImport;
    protected $completeMigrationService;
    
    public function __construct(
        OldDatabaseImportService $oldDatabaseImport,
        CompleteDatabaseMigrationService $completeMigrationService
    ) {
        $this->oldDatabaseImport = $oldDatabaseImport;
        $this->completeMigrationService = $completeMigrationService;
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
     * Migrate complete database (ALL tables)
     */
    public function migrateCoreData(Request $request)
    {
        try {
            $result = $this->completeMigrationService->migrateCompleteDatabase();
            
            return response()->json([
                'success' => true,
                'message' => 'Successfully migrated complete database (ALL tables included)',
                'data' => [
                    'users_migrated' => count($result['results']['users']),
                    'family_trees_migrated' => count($result['results']['family_trees']),
                    'education_records' => count($result['results']['education']),
                    'deceased_profiles' => count($result['results']['deceased_profiles']),
                    'media_files' => count($result['results']['media']),
                    'cities' => count($result['results']['cities']),
                    'additional_tables' => count($result['results']['additional_tables']),
                    'summary' => $result['summary']
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to migrate complete database: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to migrate complete database: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Migrate a specific tree
     */
    public function migrateTree(Request $request, $treeId)
    {
        try {
            // For single tree, we'll do complete migration
            $result = $this->completeMigrationService->migrateCompleteDatabase();
            
            return response()->json([
                'success' => true,
                'message' => "Successfully migrated complete database including tree {$treeId}",
                'data' => [
                    'users_migrated' => count($result['results']['users']),
                    'family_trees_migrated' => count($result['results']['family_trees']),
                    'education_records' => count($result['results']['education']),
                    'deceased_profiles' => count($result['results']['deceased_profiles']),
                    'media_files' => count($result['results']['media']),
                    'cities' => count($result['results']['cities']),
                    'additional_tables' => count($result['results']['additional_tables']),
                    'summary' => $result['summary']
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to migrate complete database: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => "Failed to migrate complete database: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Migrate all trees
     */
    public function migrateAllTrees(Request $request)
    {
        try {
            // For all trees, we'll do complete migration
            $result = $this->completeMigrationService->migrateCompleteDatabase();
            
            return response()->json([
                'success' => true,
                'message' => "Successfully migrated complete database including all family trees",
                'data' => [
                    'users_migrated' => count($result['results']['users']),
                    'family_trees_migrated' => count($result['results']['family_trees']),
                    'education_records' => count($result['results']['education']),
                    'deceased_profiles' => count($result['results']['deceased_profiles']),
                    'media_files' => count($result['results']['media']),
                    'cities' => count($result['results']['cities']),
                    'additional_tables' => count($result['results']['additional_tables']),
                    'summary' => $result['summary']
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to migrate complete database: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to migrate complete database: ' . $e->getMessage()
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
            'total_media' => DB::table('media')->count(),
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