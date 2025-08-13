<?php

namespace App\Services;

use App\Models\User;
use App\Models\FamilyTreeLayout;
use App\Models\FamilyTreeNode;
use App\Models\FamilyTreeEdge;
use App\Models\Education;
use App\Models\DeceasedProfile;
use App\Models\Media;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Exception;

class CompleteDatabaseMigrationService
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
     * Migrate ALL tables from old database to new database
     */
    public function migrateCompleteDatabase()
    {
        try {
            Log::info("Starting complete database migration");
            
            $migrationResults = [];
            
            // 1. Migrate Users (anagrafica → users)
            $migrationResults['users'] = $this->migrateUsers();
            Log::info("Migrated users: " . count($migrationResults['users']));
            
            // 2. Migrate Family Trees
            $migrationResults['family_trees'] = $this->migrateAllFamilyTrees();
            Log::info("Migrated family trees: " . count($migrationResults['family_trees']));
            
            // 3. Migrate Education Data
            $migrationResults['education'] = $this->migrateEducationData();
            Log::info("Migrated education records: " . count($migrationResults['education']));
            
            // 4. Migrate Deceased Profiles
            $migrationResults['deceased_profiles'] = $this->migrateDeceasedProfiles();
            Log::info("Migrated deceased profiles: " . count($migrationResults['deceased_profiles']));
            
            // 5. Migrate Media Files
            $migrationResults['media'] = $this->migrateMediaFiles();
            Log::info("Migrated media files: " . count($migrationResults['media']));
            
            // 6. Migrate Cities Data
            $migrationResults['cities'] = $this->migrateCitiesData();
            Log::info("Migrated cities: " . count($migrationResults['cities']));
            
            // 7. Migrate Additional Tables
            $migrationResults['additional_tables'] = $this->migrateAdditionalTables();
            
            Log::info("Complete database migration finished successfully");
            
            return [
                'success' => true,
                'message' => 'Complete database migration successful',
                'results' => $migrationResults,
                'summary' => $this->generateMigrationSummary($migrationResults)
            ];
            
        } catch (Exception $e) {
            Log::error("Complete database migration failed: " . $e->getMessage());
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
                    // Create new user
                    $newUser = User::create([
                        'name' => trim($oldUser->nome . ' ' . $oldUser->cognome),
                        'email' => $oldUser->mail ?: 'user_' . $oldUser->id . '@migrated.com',
                        'username' => $oldUser->detta ?: 'user_' . $oldUser->id,
                        'password' => bcrypt('migrated_' . $oldUser->id), // Temporary password
                        'date_of_birth' => $oldUser->datanascita,
                        'location' => $oldUser->cittaresidenza ?: $oldUser->cittanascita,
                        'gender' => $this->mapGender($oldUser->sesso),
                        'bio' => $this->generateBio($oldUser),
                        'height_cm' => null, // Not in old data
                        'weight_kg' => null, // Not in old data
                        'university' => null, // Will be filled from education data
                        'field_of_study' => null, // Will be filled from education data
                        'education_period' => null, // Will be filled from education data
                        'passion' => null, // Not in old data
                        'profession' => $oldUser->descrizioneoccupazione,
                        'mission' => null, // Not in old data
                        'calling' => null, // Not in old data
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
                            'old_data' => $oldUser,
                            'migrated_from' => 'anagrafica',
                            'migration_date' => now()->toISOString(),
                            'original_fields' => [
                                'nome' => $oldUser->nome,
                                'cognome' => $oldUser->cognome,
                                'detta' => $oldUser->detta,
                                'sesso' => $oldUser->sesso,
                                'datanascita' => $oldUser->datanascita,
                                'datamorte' => $oldUser->datamorte,
                                'cittanascita' => $oldUser->cittanascita,
                                'provincianascita' => $oldUser->provincianascita,
                                'nazionenascita' => $oldUser->nazionenascita,
                                'tel' => $oldUser->tel,
                                'cittaresidenza' => $oldUser->cittaresidenza,
                                'provinciaresidenza' => $oldUser->provinciaresidenza,
                                'nazioneresidenza' => $oldUser->nazioneresidenza,
                                'linkfb' => $oldUser->linkfb,
                                'linktweter' => $oldUser->linktweter,
                                'linkyoutube' => $oldUser->linkyoutube,
                                'curriculum' => $oldUser->curriculum,
                                'foto' => $oldUser->foto,
                                'lifeextention' => $oldUser->lifeextention,
                                'crediti' => $oldUser->crediti,
                                'occupazione' => $oldUser->occupazione,
                                'descrizioneoccupazione' => $oldUser->descrizioneoccupazione,
                                'cf' => $oldUser->cf,
                                'titolodistudio' => $oldUser->titolodistudio,
                                'causa_decesso' => $oldUser->causa_decesso,
                                'numeroaborti' => $oldUser->numeroaborti
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
                ],
                'old_tree_data' => $oldTree
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
                    'person_data' => $personData,
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
     * Migrate education data
     */
    private function migrateEducationData()
    {
        // Get users who have education information
        $usersWithEducation = DB::table('anagrafica')
            ->whereNotNull('titolodistudio')
            ->where('titolodistudio', '!=', '')
            ->get();
            
        $migratedEducation = [];
        
        foreach ($usersWithEducation as $user) {
            try {
                // Find corresponding new user
                $newUser = User::where('custom_data->old_id', $user->id)->first();
                
                if ($newUser) {
                    $education = Education::create([
                        'user_id' => $newUser->id,
                        'degree' => $user->titolodistudio,
                        'institution' => null, // Not in old data
                        'field_of_study' => null, // Not in old data
                        'start_date' => null, // Not in old data
                        'end_date' => null, // Not in old data
                        'description' => null, // Not in old data
                        'custom_data' => [
                            'old_user_id' => $user->id,
                            'migrated_from' => 'anagrafica',
                            'migration_date' => now()->toISOString()
                        ]
                    ]);
                    
                    $migratedEducation[] = [
                        'old_user_id' => $user->id,
                        'new_user_id' => $newUser->id,
                        'education_id' => $education->id,
                        'degree' => $user->titolodistudio,
                        'status' => 'created'
                    ];
                    
                    // Update user's education fields
                    $newUser->update([
                        'university' => null,
                        'field_of_study' => null,
                        'education_period' => null
                    ]);
                }
                
            } catch (Exception $e) {
                Log::error("Failed to migrate education for user {$user->id}: " . $e->getMessage());
                $migratedEducation[] = [
                    'old_user_id' => $user->id,
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $migratedEducation;
    }
    
    /**
     * Migrate deceased profiles
     */
    private function migrateDeceasedProfiles()
    {
        $deceasedUsers = DB::table('anagrafica')
            ->whereNotNull('datamorte')
            ->get();
            
        $migratedDeceased = [];
        
        foreach ($deceasedUsers as $user) {
            try {
                // Find corresponding new user
                $newUser = User::where('custom_data->old_id', $user->id)->first();
                
                if ($newUser) {
                    $deceasedProfile = DeceasedProfile::create([
                        'user_id' => $newUser->id,
                        'date_of_death' => $user->datamorte,
                        'cause_of_death' => $user->causa_decesso,
                        'place_of_death' => null, // Not in old data
                        'funeral_services' => null, // Not in old data
                        'obituary' => null, // Not in old data
                        'memorial_services' => null, // Not in old data
                        'custom_data' => [
                            'old_user_id' => $user->id,
                            'migrated_from' => 'anagrafica',
                            'migration_date' => now()->toISOString()
                        ]
                    ]);
                    
                    $migratedDeceased[] = [
                        'old_user_id' => $user->id,
                        'new_user_id' => $newUser->id,
                        'deceased_profile_id' => $deceasedProfile->id,
                        'date_of_death' => $user->datamorte,
                        'status' => 'created'
                    ];
                }
                
            } catch (Exception $e) {
                Log::error("Failed to migrate deceased profile for user {$user->id}: " . $e->getMessage());
                $migratedDeceased[] = [
                    'old_user_id' => $user->id,
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $migratedDeceased;
    }
    
    /**
     * Migrate media files
     */
    private function migrateMediaFiles()
    {
        $usersWithMedia = DB::table('anagrafica')
            ->where('immagine', '>', 0)
            ->orWhere('foto', '>', 0)
            ->orWhere('curriculum', '>', 0)
            ->get();
            
        $migratedMedia = [];
        
        foreach ($usersWithMedia as $user) {
            try {
                // Find corresponding new user
                $newUser = User::where('custom_data->old_id', $user->id)->first();
                
                if ($newUser) {
                    // Create media records for different types
                    if ($user->immagine > 0) {
                        $media = Media::create([
                            'user_id' => $newUser->id,
                            'type' => 'profile_picture',
                            'file_path' => null, // Will need to be mapped
                            'file_url' => null, // Will need to be mapped
                            'title' => 'Profile Picture',
                            'description' => 'Migrated profile picture',
                            'custom_data' => [
                                'old_user_id' => $user->id,
                                'old_media_id' => $user->immagine,
                                'migrated_from' => 'anagrafica',
                                'migration_date' => now()->toISOString()
                            ]
                        ]);
                        
                        $migratedMedia[] = [
                            'type' => 'profile_picture',
                            'old_user_id' => $user->id,
                            'new_user_id' => $newUser->id,
                            'media_id' => $media->id,
                            'status' => 'created'
                        ];
                    }
                    
                    if ($user->foto > 0) {
                        $media = Media::create([
                            'user_id' => $newUser->id,
                            'type' => 'photo',
                            'file_path' => null,
                            'file_url' => null,
                            'title' => 'Photo',
                            'description' => 'Migrated photo',
                            'custom_data' => [
                                'old_user_id' => $user->id,
                                'old_media_id' => $user->foto,
                                'migrated_from' => 'anagrafica',
                                'migration_date' => now()->toISOString()
                            ]
                        ]);
                        
                        $migratedMedia[] = [
                            'type' => 'photo',
                            'old_user_id' => $user->id,
                            'new_user_id' => $newUser->id,
                            'media_id' => $media->id,
                            'status' => 'created'
                        ];
                    }
                    
                    if ($user->curriculum > 0) {
                        $media = Media::create([
                            'user_id' => $newUser->id,
                            'type' => 'document',
                            'file_path' => null,
                            'file_url' => null,
                            'title' => 'Curriculum',
                            'description' => 'Migrated curriculum',
                            'custom_data' => [
                                'old_user_id' => $user->id,
                                'old_media_id' => $user->curriculum,
                                'migrated_from' => 'anagrafica',
                                'migration_date' => now()->toISOString()
                            ]
                        ]);
                        
                        $migratedMedia[] = [
                            'type' => 'curriculum',
                            'old_user_id' => $user->id,
                            'new_user_id' => $newUser->id,
                            'media_id' => $media->id,
                            'status' => 'created'
                        ];
                    }
                }
                
            } catch (Exception $e) {
                Log::error("Failed to migrate media for user {$user->id}: " . $e->getMessage());
                $migratedMedia[] = [
                    'old_user_id' => $user->id,
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $migratedMedia;
    }
    
    /**
     * Migrate cities data
     */
    private function migrateCitiesData()
    {
        $cities = DB::table('citta')->get();
        $migratedCities = [];
        
        foreach ($cities as $city) {
            $migratedCities[] = [
                'old_id' => $city->id,
                'cod_istat' => $city->cod_istat,
                'nome' => $city->nome,
                'provincia_id' => $city->provincia_id,
                'status' => 'preserved'
            ];
        }
        
        return $migratedCities;
    }
    
    /**
     * Migrate additional tables
     */
    private function migrateAdditionalTables()
    {
        $additionalTables = [];
        
        // Check for other tables that might exist
        $allTables = Schema::getAllTables();
        
        foreach ($allTables as $table) {
            $tableName = $table->name;
            
            // Skip tables we've already migrated
            if (in_array($tableName, [
                'anagrafica', 'genealogical_tree', 'genealogical_tree_person',
                'genealogical_tree_template', 'genealogical_tree_template_item', 'citta'
            ])) {
                continue;
            }
            
            // Check if it's an old system table
            if (str_contains($tableName, 'genealogical') || 
                str_contains($tableName, 'anagrafica') ||
                str_contains($tableName, 'citta')) {
                
                $recordCount = DB::table($tableName)->count();
                $additionalTables[] = [
                    'table_name' => $tableName,
                    'record_count' => $recordCount,
                    'status' => 'preserved',
                    'note' => 'Table preserved for reference'
                ];
            }
        }
        
        return $additionalTables;
    }
    
    /**
     * Generate migration summary
     */
    private function generateMigrationSummary($results)
    {
        $summary = [
            'total_users_migrated' => count($results['users']),
            'total_family_trees_migrated' => count($results['family_trees']),
            'total_education_records' => count($results['education']),
            'total_deceased_profiles' => count($results['deceased_profiles']),
            'total_media_files' => count($results['media']),
            'total_cities' => count($results['cities']),
            'total_additional_tables' => count($results['additional_tables']),
            'migration_date' => now()->toISOString(),
            'status' => 'completed'
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