<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related user (for notifications involving other users).
     */
    public function relatedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'data->user_id');
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread(): void
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Get the time ago string for the notification.
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get the icon for the notification type.
     */
    public function getIconAttribute(): string
    {
        return match ($this->type) {
            'profile_view' => 'eye',
            'connection_request' => 'user-plus',
            'connection_accepted' => 'user-check',
            'tribute_added' => 'heart',
            'subscription_reminder' => 'clock',
            'subscription_expired' => 'alert-triangle',
            'new_message' => 'message-circle',
            'legacy_reminder' => 'book-open',
            default => 'bell',
        };
    }

    /**
     * Get the color for the notification type.
     */
    public function getColorAttribute(): string
    {
        return match ($this->type) {
            'profile_view' => 'text-blue-600',
            'connection_request' => 'text-green-600',
            'connection_accepted' => 'text-green-600',
            'tribute_added' => 'text-pink-600',
            'subscription_reminder' => 'text-yellow-600',
            'subscription_expired' => 'text-red-600',
            'new_message' => 'text-purple-600',
            'legacy_reminder' => 'text-indigo-600',
            default => 'text-gray-600',
        };
    }

    /**
     * Scope to get unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope to get read notifications.
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Scope to get recent notifications.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
