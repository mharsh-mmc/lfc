<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyTreeEdge extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'from_node_id',
        'to_node_id',
        'relationship_type',
        'edge_type',
        'edge_data',
    ];

    protected $casts = [
        'edge_type' => 'string',
        'edge_data' => 'array',
    ];

    /**
     * Get the user who owns this family tree
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the source node
     */
    public function fromNode(): BelongsTo
    {
        return $this->belongsTo(FamilyTreeNode::class, 'from_node_id');
    }

    /**
     * Get the target node
     */
    public function toNode(): BelongsTo
    {
        return $this->belongsTo(FamilyTreeNode::class, 'to_node_id');
    }
}
