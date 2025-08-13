<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class FamilyRelationshipInferenceService
{
    /**
     * Infer relationships from old tree structure
     */
    public function inferRelationships($oldTreeId)
    {
        // Get all people in the tree
        $treePeople = DB::table('genealogical_tree_person')
            ->where('gtid', $oldTreeId)
            ->orderBy('traid', 'asc')
            ->orderBy('priority', 'asc')
            ->get();
            
        $relationships = [];
        
        // Group people by position
        $groupedPeople = $treePeople->groupBy('position');
        
        // Infer parent-child relationships
        $relationships = array_merge(
            $relationships,
            $this->inferParentChildRelationships($groupedPeople)
        );
        
        // Infer sibling relationships
        $relationships = array_merge(
            $relationships,
            $this->inferSiblingRelationships($groupedPeople)
        );
        
        // Infer spousal relationships
        $relationships = array_merge(
            $relationships,
            $this->inferSpousalRelationships($groupedPeople)
        );
        
        return $relationships;
    }
    
    /**
     * Infer parent-child relationships
     */
    private function inferParentChildRelationships($groupedPeople)
    {
        $relationships = [];
        
        // People in 'alto' (top) are likely parents
        $parents = $groupedPeople->get('alto', collect());
        // People in 'basso' (bottom) are likely children
        $children = $groupedPeople->get('basso', collect());
        
        foreach ($parents as $parent) {
            foreach ($children as $child) {
                $relationships[] = [
                    'from_node_id' => $parent->pid,
                    'to_node_id' => $child->pid,
                    'relationship_type' => 'family',
                    'edge_type' => 'bezier',
                    'edge_data' => [
                        'inferred' => true,
                        'confidence' => 'high',
                        'source' => 'position_inference',
                        'relationship' => 'parent_child'
                    ]
                ];
            }
        }
        
        return $relationships;
    }
    
    /**
     * Infer sibling relationships
     */
    private function inferSiblingRelationships($groupedPeople)
    {
        $relationships = [];
        
        // People in the same position group are likely siblings
        foreach ($groupedPeople as $position => $people) {
            if (count($people) > 1) {
                $peopleArray = $people->toArray();
                
                for ($i = 0; $i < count($peopleArray) - 1; $i++) {
                    for ($j = $i + 1; $j < count($peopleArray); $j++) {
                        $relationships[] = [
                            'from_node_id' => $peopleArray[$i]['pid'],
                            'to_node_id' => $peopleArray[$j]['pid'],
                            'relationship_type' => 'family',
                            'edge_type' => 'bezier',
                            'edge_data' => [
                                'inferred' => true,
                                'confidence' => 'medium',
                                'source' => 'position_inference',
                                'relationship' => 'sibling'
                            ]
                        ];
                    }
                }
            }
        }
        
        return $relationships;
    }
    
    /**
     * Infer spousal relationships
     */
    private function inferSpousalRelationships($groupedPeople)
    {
        $relationships = [];
        
        // People in 'destra' (right) might be spouses
        $spouses = $groupedPeople->get('destra', collect());
        
        if ($spouses->count() > 1) {
            $spousesArray = $spouses->toArray();
            
            // Connect spouses to main person (usually in center or alto)
            $mainPerson = $groupedPeople->get('alto', collect())->first();
            
            if ($mainPerson) {
                foreach ($spouses as $spouse) {
                    $relationships[] = [
                        'from_node_id' => $mainPerson->pid,
                        'to_node_id' => $spouse->pid,
                        'relationship_type' => 'marriage',
                        'edge_type' => 'bezier',
                        'edge_data' => [
                            'inferred' => true,
                            'confidence' => 'medium',
                            'source' => 'position_inference',
                            'relationship' => 'spouse'
                        ]
                    ];
                }
            }
        }
        
        return $relationships;
    }
    
    /**
     * Get relationship confidence level
     */
    public function getRelationshipConfidence($position, $priority)
    {
        return match($position) {
            'alto' => 'high',      // Parents are usually clearly defined
            'basso' => 'high',     // Children are usually clearly defined
            'sinistra' => 'medium', // Siblings might vary
            'destra' => 'medium',   // Spouses might vary
            default => 'low'
        };
    }
}