<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Education extends Model
{
    protected $fillable = [
        'user_id',
        'institution',
        'degree',
        'field_of_study',
        'period',
        'description',
        'is_current',
        'order',
    ];

    protected $casts = [
        'is_current' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
