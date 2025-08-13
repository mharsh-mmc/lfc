<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FamilyTreeNode extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile_id',
        'relation',
        'x_position',
        'y_position',
        'custom_data',
    ];

    protected $casts = [
        'custom_data' => 'array',
        'x_position' => 'integer',
        'y_position' => 'integer',
    ];

    /**
     * Get the user who owns this family tree
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the profile associated with this node
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(User::class, 'profile_id');
    }

    /**
     * Get outgoing edges from this node
     */
    public function outgoingEdges(): HasMany
    {
        return $this->hasMany(FamilyTreeEdge::class, 'from_node_id');
    }

    /**
     * Get incoming edges to this node
     */
    public function incomingEdges(): HasMany
    {
        return $this->hasMany(FamilyTreeEdge::class, 'to_node_id');
    }

    /**
     * Get all edges connected to this node
     */
    public function allEdges()
    {
        return $this->outgoingEdges->merge($this->incomingEdges);
    }
}
