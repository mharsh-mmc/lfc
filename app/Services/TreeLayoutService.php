<?php

namespace App\Services;

use App\Models\FamilyTreeNode;
use App\Models\FamilyTreeEdge;
use Illuminate\Support\Collection;

class TreeLayoutService
{
    /**
     * Generate all layout types
     */
    public function generateAllLayouts(Collection $nodes, Collection $edges): array
    {
        return [
            'vertical' => $this->generateVerticalLayout($nodes, $edges),
            'horizontal' => $this->generateHorizontalLayout($nodes, $edges),
            'circular' => $this->generateCircularLayout($nodes, $edges),
        ];
    }

    /**
     * Generate vertical hierarchical layout
     */
    public function generateVerticalLayout(Collection $nodes, Collection $edges): array
    {
        $layout = [];
        $centerNode = $nodes->where('relation', 'self')->first();
        
        if (!$centerNode) {
            return $this->generateDefaultLayout($nodes);
        }

        $centerX = 0;
        $centerY = 0;
        $spacing = 200;
        $levelSpacing = 150;

        // Center node
        $layout[$centerNode->id] = [
            'x' => $centerX,
            'y' => $centerY,
        ];

        // Parents (above)
        $parents = $nodes->whereIn('relation', ['parent', 'grandparent']);
        $parentCount = $parents->count();
        $startX = -($parentCount - 1) * $spacing / 2;
        
        foreach ($parents as $index => $parent) {
            $layout[$parent->id] = [
                'x' => $startX + ($index * $spacing),
                'y' => $centerY - $levelSpacing,
            ];
        }

        // Children (below)
        $children = $nodes->whereIn('relation', ['child', 'grandchild']);
        $childCount = $children->count();
        $startX = -($childCount - 1) * $spacing / 2;
        
        foreach ($children as $index => $child) {
            $layout[$child->id] = [
                'x' => $startX + ($index * $spacing),
                'y' => $centerY + $levelSpacing,
            ];
        }

        // Siblings (same level)
        $siblings = $nodes->whereIn('relation', ['sibling', 'cousin']);
        $siblingCount = $siblings->count();
        $startX = -($siblingCount - 1) * $spacing / 2;
        
        foreach ($siblings as $index => $sibling) {
            $layout[$sibling->id] = [
                'x' => $startX + ($index * $spacing),
                'y' => $centerY,
            ];
        }

        // Spouse (same level, slightly offset)
        $spouse = $nodes->where('relation', 'spouse')->first();
        if ($spouse) {
            $layout[$spouse->id] = [
                'x' => $centerX + $spacing,
                'y' => $centerY,
            ];
        }

        // Other relations
        $others = $nodes->whereNotIn('relation', ['self', 'parent', 'grandparent', 'child', 'grandchild', 'sibling', 'cousin', 'spouse']);
        foreach ($others as $other) {
            $layout[$other->id] = [
                'x' => $centerX + (rand(-300, 300)),
                'y' => $centerY + (rand(-200, 200)),
            ];
        }

        return $layout;
    }

    /**
     * Generate horizontal hierarchical layout
     */
    public function generateHorizontalLayout(Collection $nodes, Collection $edges): array
    {
        $layout = [];
        $centerNode = $nodes->where('relation', 'self')->first();
        
        if (!$centerNode) {
            return $this->generateDefaultLayout($nodes);
        }

        $centerX = 0;
        $centerY = 0;
        $spacing = 200;
        $levelSpacing = 150;

        // Center node
        $layout[$centerNode->id] = [
            'x' => $centerX,
            'y' => $centerY,
        ];

        // Parents (left)
        $parents = $nodes->whereIn('relation', ['parent', 'grandparent']);
        $parentCount = $parents->count();
        $startY = -($parentCount - 1) * $spacing / 2;
        
        foreach ($parents as $index => $parent) {
            $layout[$parent->id] = [
                'x' => $centerX - $levelSpacing,
                'y' => $startY + ($index * $spacing),
            ];
        }

        // Children (right)
        $children = $nodes->whereIn('relation', ['child', 'grandchild']);
        $childCount = $children->count();
        $startY = -($childCount - 1) * $spacing / 2;
        
        foreach ($children as $index => $child) {
            $layout[$child->id] = [
                'x' => $centerX + $levelSpacing,
                'y' => $startY + ($index * $spacing),
            ];
        }

        // Siblings (same level)
        $siblings = $nodes->whereIn('relation', ['sibling', 'cousin']);
        $siblingCount = $siblings->count();
        $startY = -($siblingCount - 1) * $spacing / 2;
        
        foreach ($siblings as $index => $sibling) {
            $layout[$sibling->id] = [
                'x' => $centerX,
                'y' => $startY + ($index * $spacing),
            ];
        }

        // Spouse (same level, slightly offset)
        $spouse = $nodes->where('relation', 'spouse')->first();
        if ($spouse) {
            $layout[$spouse->id] = [
                'x' => $centerX,
                'y' => $centerY + $spacing,
            ];
        }

        // Other relations
        $others = $nodes->whereNotIn('relation', ['self', 'parent', 'grandparent', 'child', 'grandchild', 'sibling', 'cousin', 'spouse']);
        foreach ($others as $other) {
            $layout[$other->id] = [
                'x' => $centerX + (rand(-200, 200)),
                'y' => $centerY + (rand(-300, 300)),
            ];
        }

        return $layout;
    }

    /**
     * Generate circular layout
     */
    public function generateCircularLayout(Collection $nodes, Collection $edges): array
    {
        $layout = [];
        $centerNode = $nodes->where('relation', 'self')->first();
        
        if (!$centerNode) {
            return $this->generateDefaultLayout($nodes);
        }

        $centerX = 0;
        $centerY = 0;
        $radius = 200;
        $nodeCount = $nodes->count() - 1; // Exclude center node

        if ($nodeCount === 0) {
            return $layout;
        }

        // Center node
        $layout[$centerNode->id] = [
            'x' => $centerX,
            'y' => $centerY,
        ];

        // Arrange other nodes in a circle
        $angleStep = 2 * M_PI / $nodeCount;
        $currentAngle = 0;

        foreach ($nodes->where('id', '!=', $centerNode->id) as $node) {
            $layout[$node->id] = [
                'x' => $centerX + ($radius * cos($currentAngle)),
                'y' => $centerY + ($radius * sin($currentAngle)),
            ];
            $currentAngle += $angleStep;
        }

        return $layout;
    }

    /**
     * Generate default layout when no center node exists
     */
    protected function generateDefaultLayout(Collection $nodes): array
    {
        $layout = [];
        $spacing = 150;
        $cols = ceil(sqrt($nodes->count()));
        $rows = ceil($nodes->count() / $cols);

        foreach ($nodes as $index => $node) {
            $col = $index % $cols;
            $row = floor($index / $cols);
            
            $layout[$node->id] = [
                'x' => ($col - ($cols - 1) / 2) * $spacing,
                'y' => ($row - ($rows - 1) / 2) * $spacing,
            ];
        }

        return $layout;
    }

    /**
     * Get layout by type
     */
    public function getLayoutByType(string $type, Collection $nodes, Collection $edges): array
    {
        return match ($type) {
            'vertical' => $this->generateVerticalLayout($nodes, $edges),
            'horizontal' => $this->generateHorizontalLayout($nodes, $edges),
            'circular' => $this->generateCircularLayout($nodes, $edges),
            default => $this->generateDefaultLayout($nodes),
        };
    }
}
