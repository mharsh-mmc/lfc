<?php

namespace App\Services;

class FamilyTreePositionConverter
{
    /**
     * Convert old position system to VueFlow coordinates
     */
    private $positionMap = [
        'alto' => ['x' => 0, 'y' => -200],      // Top
        'basso' => ['x' => 0, 'y' => 200],     // Bottom
        'sinistra' => ['x' => -300, 'y' => 0],  // Left
        'destra' => ['x' => 300, 'y' => 0],     // Right
    ];
    
    /**
     * Convert old position to VueFlow coordinates
     */
    public function convertPosition($position, $priority, $maxPersons)
    {
        $basePosition = $this->positionMap[$position] ?? ['x' => 0, 'y' => 0];
        
        // Calculate offset based on priority and number of people
        $offset = $this->calculateOffset($position, $priority, $maxPersons);
        
        return [
            'x' => $basePosition['x'] + $offset['x'],
            'y' => $basePosition['y'] + $offset['y']
        ];
    }
    
    /**
     * Calculate offset based on position and priority
     */
    private function calculateOffset($position, $priority, $maxPersons)
    {
        $spacing = 150; // Distance between nodes
        
        switch ($position) {
            case 'alto':
            case 'basso':
                return ['x' => ($priority - 1) * $spacing, 'y' => 0];
            case 'sinistra':
            case 'destra':
                return ['x' => 0, 'y' => ($priority - 1) * $spacing];
            default:
                return ['x' => 0, 'y' => 0];
        }
    }
    
    /**
     * Get spacing for different positions
     */
    public function getSpacing($position)
    {
        return match($position) {
            'alto', 'basso' => 200,    // Horizontal spacing for top/bottom
            'sinistra', 'destra' => 150, // Vertical spacing for left/right
            default => 150
        };
    }
}