<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;

class Media extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'user_media';

    protected $fillable = [
        'user_id',
        'media_type',
        'title',
        'description',
        'is_private',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_private' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user that owns the media
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by user
     */
    public function scopeOfUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by media type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('media_type', $type);
    }

    /**
     * Scope to show only public media
     */
    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }

    /**
     * Scope to order by recent
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Get the collection name for this media type
     */
    private function getCollectionName(): string
    {
        $collectionMap = [
            'video' => 'videos',
            'image' => 'images',
            'letter' => 'letters',
            'document' => 'documents'
        ];
        
        return $collectionMap[$this->media_type] ?? $this->media_type;
    }

    /**
     * Register media collections with Spatie Media Library
     */
    public function registerMediaCollections(): void
    {
        $collectionName = $this->getCollectionName();
        
        $this->addMediaCollection($collectionName)
            ->singleFile()
            ->acceptsMimeTypes($this->getAcceptedMimeTypes());
    }

    /**
     * Get accepted MIME types for this media type
     */
    private function getAcceptedMimeTypes(): array
    {
        return match($this->media_type) {
            'video' => [
                'video/mp4',
                'video/mov',
                'video/avi',
                'video/wmv',
                'video/flv',
                'video/webm'
            ],
            'image' => [
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/webp',
                'image/gif'
            ],
            'letter' => [
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/webp'
            ],
            'document' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ],
            default => ['*/*']
        };
    }

    /**
     * Get the media URL from Spatie Media Library
     */
    public function getMediaUrlAttribute(): ?string
    {
        $collectionName = $this->getCollectionName();
        $spatieMedia = $this->getFirstMedia($collectionName);
        return $spatieMedia ? $spatieMedia->getUrl() : null;
    }

    /**
     * Get the thumbnail URL from Spatie Media Library
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        $collectionName = $this->getCollectionName();
        $spatieMedia = $this->getFirstMedia($collectionName);
        return $spatieMedia ? $spatieMedia->getUrl('thumbnail') : null;
    }

    /**
     * Get the file size from Spatie Media Library
     */
    public function getFileSizeAttribute(): ?string
    {
        $collectionName = $this->getCollectionName();
        $spatieMedia = $this->getFirstMedia($collectionName);
        if (!$spatieMedia) return null;

        $bytes = $spatieMedia->size;
        if ($bytes <= 0) return '0 B';

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $base = log($bytes, 1024);
        $unit = $units[floor($base)];

        return round(pow(1024, $base - floor($base)), 2) . ' ' . $unit;
    }

    /**
     * Get the file type from Spatie Media Library
     */
    public function getFileTypeAttribute(): ?string
    {
        $collectionName = $this->getCollectionName();
        $spatieMedia = $this->getFirstMedia($collectionName);
        return $spatieMedia ? $spatieMedia->mime_type : null;
    }

    /**
     * Get the file name from Spatie Media Library
     */
    public function getFileNameAttribute(): ?string
    {
        $collectionName = $this->getCollectionName();
        $spatieMedia = $this->getFirstMedia($collectionName);
        return $spatieMedia ? $spatieMedia->file_name : null;
    }

    /**
     * Get the duration for videos (placeholder)
     */
    public function getDurationAttribute(): ?string
    {
        if ($this->media_type !== 'video') return null;
        
        // This is a placeholder - in production you'd extract actual duration
        // from the video file using FFmpeg or similar
        return null;
    }

    /**
     * Boot method to handle model events
     */
    protected static function boot()
    {
        parent::boot();

        // Clear associated media collection when deleting
        static::deleting(function ($media) {
            $collectionName = $media->getCollectionName();
            $media->clearMediaCollection($collectionName);
        });
    }
}
