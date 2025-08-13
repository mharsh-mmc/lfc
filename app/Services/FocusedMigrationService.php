<?php

namespace App\Services;

use App\Models\User;
use App\Models\FamilyTreeLayout;
use App\Models\FamilyTreeNode;
use App\Models\FamilyTreeEdge;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class FocusedMigrationService
{
    protected $positionConverter;
    protected $relationshipInference;
    
    public function __construct(
        FamilyTreePositionConverter $positionConverter,
        FamilyRelationshipInferenceService $relationshipInference
    ) {
        $this->positionConverter = $positionConverter;
        $this->relationshipInference = $relationshipInference;
    }
    
    /**
     * Migrate ONLY users and family trees (core data)
     */
    public function migrateCoreData()
    {
        try {
            Log::info("Starting focused migration: Users + Family Trees only");
            
            $migrationResults = [];
            
            // 1. Migrate Users (anagrafica → users)
            $migrationResults['users'] = $this->migrateUsers();
            Log::info("Migrated users: " . count($migrationResults['users']));
            
            // 2. Migrate Family Trees
            $migrationResults['family_trees'] = $this->migrateAllFamilyTrees();
            Log::info("Migrated family trees: " . count($migrationResults['family_trees']));
            
            Log::info("Focused migration completed successfully");
            
            return [
                'success' => true,
                'message' => 'Core data migration successful (Users + Family Trees)',
                'results' => $migrationResults,
                'summary' => $this->generateMigrationSummary($migrationResults)
            ];
            
        } catch (Exception $e) {
            Log::error("Focused migration failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Migrate all users from anagrafica to users table
     */
    private function migrateUsers()
    {
        $oldUsers = DB::table('anagrafica')->get();
        $migratedUsers = [];
        
        foreach ($oldUsers as $oldUser) {
            try {
                // Check if user already exists
                $existingUser = User::where('email', $oldUser->mail)->first();
                
                if (!$existingUser) {
                    // Create new user with essential data only
                    $newUser = User::create([
                        'name' => trim($oldUser->nome . ' ' . $oldUser->cognome),
                        'email' => $oldUser->mail ?: 'user_' . $oldUser->id . '@migrated.com',
                        'username' => $oldUser->detta ?: 'user_' . $oldUser->id,
                        'password' => bcrypt('migrated_' . $oldUser->id), // Temporary password
                        'date_of_birth' => $oldUser->datanascita,
                        'location' => $oldUser->cittaresidenza ?: $oldUser->cittanascita,
                        'gender' => $this->mapGender($oldUser->sesso),
                        'bio' => $this->generateBio($oldUser),
                        'height_cm' => null,
                        'weight_kg' => null,
                        'university' => null,
                        'field_of_study' => null,
                        'education_period' => null,
                        'passion' => null,
                        'profession' => $oldUser->descrizioneoccupazione,
                        'mission' => null,
                        'calling' => null,
                        'connections_count' => 0,
                        'tributes_count' => 0,
                        'flowers_count' => 0,
                        'is_public' => true,
                        'last_activity' => now(),
                        'banner_path' => null,
                        'banner_url' => null,
                        'profile_picture_path' => null,
                        'profile_picture_url' => null,
                        'custom_data' => [
                            'old_id' => $oldUser->id,
                            'migrated_from' => 'anagrafica',
                            'migration_date' => now()->toISOString(),
                            // Store only essential original data
                            'original_name' => [
                                'nome' => $oldUser->nome,
                                'cognome' => $oldUser->cognome,
                                'detta' => $oldUser->detta
                            ],
                            'original_birth' => [
                                'datanascita' => $oldUser->datanascita,
                                'cittanascita' => $oldUser->cittanascita,
                                'provincianascita' => $oldUser->provincianascita,
                                'nazionenascita' => $oldUser->nazionenascita
                            ],
                            'original_contact' => [
                                'mail' => $oldUser->mail,
                                'tel' => $oldUser->tel
                            ],
                            'original_location' => [
                                'cittaresidenza' => $oldUser->cittaresidenza,
                                'provinciaresidenza' => $oldUser->provinciaresidenza,
                                'nazioneresidenza' => $oldUser->nazioneresidenza
                            ],
                            'original_profession' => [
                                'occupazione' => $oldUser->occupazione,
                                'descrizioneoccupazione' => $oldUser->descrizioneoccupazione,
                                'titolodistudio' => $oldUser->titolodistudio
                            ]
                        ]
                    ]);
                    
                    $migratedUsers[] = [
                        'old_id' => $oldUser->id,
                        'new_id' => $newUser->id,
                        'name' => $newUser->name,
                        'email' => $newUser->email,
                        'status' => 'created'
                    ];
                } else {
                    $migratedUsers[] = [
                        'old_id' => $oldUser->id,
                        'new_id' => $existingUser->id,
                        'name' => $existingUser->name,
                        'email' => $existingUser->email,
                        'status' => 'already_exists'
                    ];
                }
                
            } catch (Exception $e) {
                Log::error("Failed to migrate user {$oldUser->id}: " . $e->getMessage());
                $migratedUsers[] = [
                    'old_id' => $oldUser->id,
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $migratedUsers;
    }
    
    /**
     * Migrate all family trees
     */
    private function migrateAllFamilyTrees()
    {
        $oldTrees = DB::table('genealogical_tree')
            ->where('flag_active', 1)
            ->get();
            
        $migratedTrees = [];
        
        foreach ($oldTrees as $oldTree) {
            try {
                $result = $this->migrateSingleFamilyTree($oldTree->id);
                $migratedTrees[] = $result;
            } catch (Exception $e) {
                Log::error("Failed to migrate tree {$oldTree->id}: " . $e->getMessage());
                $migratedTrees[] = [
                    'old_tree_id' => $oldTree->id,
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $migratedTrees;
    }
    
    /**
     * Migrate a single family tree
     */
    private function migrateSingleFamilyTree($oldTreeId)
    {
        $oldTree = DB::table('genealogical_tree')
            ->where('id', $oldTreeId)
            ->where('flag_active', 1)
            ->first();
            
        if (!$oldTree) {
            throw new Exception("Old tree {$oldTreeId} not found or not active");
        }
        
        // Get template info
        $template = DB::table('genealogical_tree_template')
            ->where('id', $oldTree->gttid)
            ->first();
        
        // Create new tree layout
        $newTree = FamilyTreeLayout::create([
            'user_id' => $oldTree->pid,
            'name' => $template->title ?? 'Migrated Family Tree',
            'type' => $this->mapTemplateToLayout($template->template ?? 'st1'),
            'layout_data' => [
                'old_tree_id' => $oldTreeId,
                'old_template' => $template->template ?? 'st1',
                'migrated_at' => now()->toISOString(),
                'template_info' => [
                    'title' => $template->title ?? '',
                    'description' => $template->description ?? '',
                    'cost' => $template->cost ?? 0
                ]
            ],
            'is_default' => false
        ]);
        
        // Migrate nodes
        $nodes = $this->migrateTreeNodes($oldTreeId, $newTree);
        
        // Migrate relationships
        $edges = $this->migrateTreeRelationships($oldTreeId, $nodes);
        
        return [
            'old_tree_id' => $oldTreeId,
            'new_tree_id' => $newTree->id,
            'status' => 'success',
            'tree' => $newTree,
            'nodes_count' => count($nodes),
            'edges_count' => count($edges)
        ];
    }
    
    /**
     * Migrate tree nodes
     */
    private function migrateTreeNodes($oldTreeId, $newTree)
    {
        $treePeople = DB::table('genealogical_tree_person')
            ->where('gtid', $oldTreeId)
            ->get();
            
        $nodes = [];
        
        foreach ($treePeople as $person) {
            // Get person details
            $personData = DB::table('anagrafica')
                ->where('id', $person->pid)
                ->first();
                
            if (!$personData) {
                Log::warning("Person {$person->pid} not found in anagrafica table");
                continue;
            }
            
            // Convert position to coordinates
            $coordinates = $this->positionConverter->convertPosition(
                $person->position,
                $person->traid,
                10
            );
            
            // Create new node
            $newNode = FamilyTreeNode::create([
                'user_id' => $newTree->user_id,
                'profile_id' => $person->pid,
                'relation' => $this->mapOldRelation($person->traid),
                'x_position' => $coordinates['x'],
                'y_position' => $coordinates['y'],
                'custom_data' => [
                    'old_id' => $person->id,
                    'old_position' => $person->position,
                    'old_traid' => $person->traid,
                    'migrated_from' => 'old_system',
                    'migration_date' => now()->toISOString()
                ]
            ]);
            
            $nodes[$person->pid] = $newNode;
        }
        
        return $nodes;
    }
    
    /**
     * Migrate tree relationships
     */
    private function migrateTreeRelationships($oldTreeId, $nodes)
    {
        $inferredRelationships = $this->relationshipInference->inferRelationships($oldTreeId);
        
        $edges = [];
        
        foreach ($inferredRelationships as $relationship) {
            $fromNode = $nodes[$relationship['from_node_id']] ?? null;
            $toNode = $nodes[$relationship['to_node_id']] ?? null;
            
            if ($fromNode && $toNode) {
                $edge = FamilyTreeEdge::create([
                    'user_id' => $fromNode->user_id,
                    'from_node_id' => $fromNode->id,
                    'to_node_id' => $toNode->id,
                    'relationship_type' => $relationship['relationship_type'],
                    'edge_type' => $relationship['edge_type'],
                    'edge_data' => $relationship['edge_data']
                ]);
                
                $edges[] = $edge;
            }
        }
        
        return $edges;
    }
    
    /**
     * Generate migration summary
     */
    private function generateMigrationSummary($results)
    {
        $summary = [
            'total_users_migrated' => count($results['users']),
            'total_family_trees_migrated' => count($results['family_trees']),
            'migration_date' => now()->toISOString(),
            'status' => 'completed',
            'note' => 'Only core data migrated (Users + Family Trees)'
        ];
        
        return $summary;
    }
    
    /**
     * Helper methods
     */
    private function mapGender($oldGender)
    {
        return match($oldGender) {
            1 => 'male',
            2 => 'female',
            default => 'other'
        };
    }
    
    private function generateBio($user)
    {
        $bioParts = [];
        
        if ($user->descrizioneoccupazione) {
            $bioParts[] = "Profession: " . $user->descrizioneoccupazione;
        }
        
        if ($user->cittanascita && $user->cittanascita != '0') {
            $bioParts[] = "Born in: " . $user->cittanascita;
        }
        
        if ($user->cittaresidenza && $user->cittaresidenza != '') {
            $bioParts[] = "Lives in: " . $user->cittaresidenza;
        }
        
        return !empty($bioParts) ? implode('. ', $bioParts) . '.' : null;
    }
    
    private function mapOldRelation($traid)
    {
        $relationMap = [
            1 => 'parent',
            2 => 'child',
            3 => 'sibling',
            4 => 'spouse',
            5 => 'other'
        ];
        
        return $relationMap[$traid] ?? 'unknown';
    }
    
    private function mapTemplateToLayout($oldTemplate)
    {
        $templateMap = [
            'st1' => 'hierarchical',
            'standard_as_pdf' => 'vertical'
        ];
        
        return $templateMap[$oldTemplate] ?? 'custom';
    }
}