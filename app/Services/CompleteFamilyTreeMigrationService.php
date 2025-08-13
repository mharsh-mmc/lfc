<?php

namespace App\Services;

use App\Models\FamilyTreeLayout;
use App\Models\FamilyTreeNode;
use App\Models\FamilyTreeEdge;
use App\Models\User;
use App\Models\Education;
use App\Models\DeceasedProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class CompleteFamilyTreeMigrationService
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
     * Migrate complete family tree from old system
     */
    public function migrateCompleteFamilyTree($oldTreeId)
    {
        try {
            Log::info("Starting migration for tree ID: {$oldTreeId}");
            
            // 1. Create the tree layout
            $newTree = $this->createTreeLayout($oldTreeId);
            Log::info("Created tree layout: {$newTree->id}");
            
            // 2. Migrate all nodes (people)
            $nodes = $this->migrateNodes($oldTreeId, $newTree);
            Log::info("Migrated nodes: " . count($nodes));
            
            // 3. Infer and create relationships (edges)
            $edges = $this->migrateRelationships($oldTreeId, $nodes);
            Log::info("Created edges: " . count($edges));
            
            // 4. Create VueFlow data structure
            $vueFlowData = $this->createVueFlowData($nodes, $edges);
            
            Log::info("Successfully migrated tree {$oldTreeId} to new tree {$newTree->id}");
            
            return [
                'tree' => $newTree,
                'nodes' => $nodes,
                'edges' => $edges,
                'vueFlowData' => $vueFlowData
            ];
            
        } catch (Exception $e) {
            Log::error("Failed to migrate tree {$oldTreeId}: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create tree layout from old tree
     */
    private function createTreeLayout($oldTreeId)
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
        
        return FamilyTreeLayout::create([
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
    }
    
    /**
     * Migrate all nodes from old tree
     */
    private function migrateNodes($oldTreeId, $newTree)
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
                10 // max persons for this position
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
     * Migrate relationships and create edges
     */
    private function migrateRelationships($oldTreeId, $nodes)
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
     * Create VueFlow compatible data structure
     */
    private function createVueFlowData($nodes, $edges)
    {
        $vueFlowNodes = [];
        $vueFlowEdges = [];
        
        // Convert nodes to VueFlow format
        foreach ($nodes as $node) {
            $vueFlowNodes[] = [
                'id' => $node->id,
                'type' => 'personNode',
                'position' => [
                    'x' => $node->x_position,
                    'y' => $node->y_position
                ],
                'data' => [
                    'label' => $node->custom_data['person_data']['nome'] ?? 'Unknown',
                    'relation' => $node->relation,
                    'isOldSystem' => true,
                    'personData' => $node->custom_data['person_data']
                ]
            ];
        }
        
        // Convert edges to VueFlow format
        foreach ($edges as $edge) {
            $vueFlowEdges[] = [
                'id' => $edge->id,
                'source' => $edge->from_node_id,
                'target' => $edge->to_node_id,
                'type' => 'bezier',
                'data' => [
                    'relationship_type' => $edge->relationship_type,
                    'edge_type' => $edge->edge_type,
                    'inferred' => $edge->edge_data['inferred'] ?? false,
                    'confidence' => $edge->edge_data['confidence'] ?? 'unknown'
                ]
            ];
        }
        
        return [
            'nodes' => $vueFlowNodes,
            'edges' => $vueFlowEdges
        ];
    }
    
    /**
     * Map old relationship codes to new relation types
     */
    private function mapOldRelation($traid)
    {
        $relationMap = [
            1 => 'parent',    // alto (top)
            2 => 'child',      // basso (bottom)
            3 => 'sibling',    // sinistra (left)
            4 => 'spouse',     // destra (right)
            5 => 'other'       // other relationships
        ];
        
        return $relationMap[$traid] ?? 'unknown';
    }
    
    /**
     * Map old template types to new layout types
     */
    private function mapTemplateToLayout($oldTemplate)
    {
        $templateMap = [
            'st1' => 'hierarchical',
            'standard_as_pdf' => 'vertical'
        ];
        
        return $templateMap[$oldTemplate] ?? 'custom';
    }
}