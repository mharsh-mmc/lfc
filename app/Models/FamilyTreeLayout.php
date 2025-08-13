<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FamilyTreeLayout extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'layout_data',
        'is_default',
    ];

    protected $casts = [
        'layout_data' => 'array',
        'is_default' => 'boolean',
    ];

    /**
     * Get the user who owns this layout
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get default layout
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to get layouts by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
