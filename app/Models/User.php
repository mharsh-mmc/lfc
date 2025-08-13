<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $username
 * @property string|null $title
 * @property \Carbon\Carbon|null $date_of_birth
 * @property string|null $location
 * @property string|null $bio
 * @property int|null $height_cm
 * @property int|null $weight_kg
 * @property string|null $passion
 * @property string|null $profession
 * @property string|null $mission
 * @property string|null $calling
 * @property string|null $about_content
 * @property int $connections_count
 * @property int $tributes_count
 * @property int $flowers_count
 * @property bool $is_public
 * @property \Carbon\Carbon|null $last_activity
 * @property string|null $banner_path
 * @property string|null $banner_url
 * @property \Carbon\Carbon|null $email_verified_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class User extends Authenticatable implements HasMedia
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'password_reset_code',
        'password_reset_code_expires_at',
        'title',
        'date_of_birth',
        'location',
        'bio',
        'height_cm',
        'weight_kg',
        'passion',
        'profession',
        'mission',
        'calling',
        'about_content',
        'connections_count',
        'tributes_count',
        'flowers_count',
        'is_public',
        'last_activity',
        'banner_path',
        'banner_url',
        'profile_photo_path',
        'settings',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
        'settings', // Hide settings from automatic serialization
        'videos', // Hide media attributes from automatic serialization
        'images',
        'documents',
        // Removed profile_photo_url and banner_url from hidden array to allow them to be included in responses
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_of_birth' => 'date',
        'last_activity' => 'datetime',
        'settings' => 'array',
        'is_public' => 'boolean',
    ];

    /**
     * Get the notifications for the user.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get unread notifications count.
     */
    public function getUnreadNotificationsCountAttribute(): int
    {
        return $this->notifications()->unread()->count();
    }

    /**
     * Get recent notifications.
     */
    public function getRecentNotificationsAttribute()
    {
        return $this->notifications()->recent()->orderBy('created_at', 'desc')->take(10)->get();
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        // Removed profile_photo_url and banner_url from appends to prevent infinite loops
    ];

    /**
     * Register media collections for the user.
     * Each collection represents a different tab in the profile.
     */
    public function registerMediaCollections(): void
    {
        // Videos collection - for video tab
        $this->addMediaCollection('videos')
            ->acceptsMimeTypes([
                'video/mp4',
                'video/avi',
                'video/mov',
                'video/wmv',
                'video/flv',
                'video/webm',
                'video/mkv',
                'video/3gp',
                'audio/x-hx-aac-adts', // For some MP4 files
                'application/octet-stream' // Generic type
            ])
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('thumbnail')
                    ->width(320)
                    ->height(240)
                    ->sharpen(10);
            });

        // Images collection - for image gallery tab
        $this->addMediaCollection('images')
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/webp',
                'image/gif'
            ])
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('thumbnail')
                    ->width(320)
                    ->height(240)
                    ->sharpen(10);
            });

        // Letters collection - for letters tab (images and PDFs)
        $this->addMediaCollection('letters')
            ->acceptsMimeTypes([
                'application/pdf',
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/webp',
                'image/gif'
            ])
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('thumbnail')
                    ->width(320)
                    ->height(240)
                    ->sharpen(10);
            });

        // Documents collection - for documents tab (various file types)
        $this->addMediaCollection('documents')
            ->acceptsMimeTypes([
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/plain',
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/webp',
                'image/gif'
            ])
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('thumbnail')
                    ->width(320)
                    ->height(240)
                    ->sharpen(10);
            });

        // Profile photos collection
        $this->addMediaCollection('profile-photos')
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/webp'
            ])
            ->singleFile();
            // Temporarily disabled conversions to fix image display issues
            // ->registerMediaConversions(function (Media $media) {
            //     $this->addMediaConversion('profile')
            //         ->width(200)
            //         ->height(200)
            //         ->sharpen(10);
            // });

        // Banners collection
        $this->addMediaCollection('banners')
            ->acceptsMimeTypes([
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/webp'
            ])
            ->singleFile();
            // Temporarily disabled conversions to fix image display issues
            // ->registerMediaConversions(function (Media $media) {
            //     $this->addMediaConversion('banner')
            //         ->width(1200)
            //         ->height(400)
            //         ->sharpen(10);
            // });
    }

    /**
     * Check if user can add more files to a collection.
     */
    public function canAddToCollection(string $collectionName): bool
    {
        $limits = [
            'videos' => 20,
            'images' => 50,
            'letters' => 50,
            'documents' => 20,
        ];

        if (!isset($limits[$collectionName])) {
            return true;
        }

        return $this->getMedia($collectionName)->count() < $limits[$collectionName];
    }

    /**
     * Get all user media records.
     */
    public function userMedia(): HasMany
    {
        return $this->hasMany(\App\Models\Media::class);
    }

    /**
     * Get videos for the user.
     */
    public function videos()
    {
        return $this->userMedia()->where('media_type', 'video');
    }

    /**
     * Get images for the user.
     */
    public function images()
    {
        return $this->userMedia()->where('media_type', 'image');
    }

    /**
     * Get letters for the user.
     */
    public function letters()
    {
        return $this->userMedia()->where('media_type', 'letter');
    }

    /**
     * Get the education entries for the user.
     */
    public function education(): HasMany
    {
        return $this->hasMany(Education::class)->orderBy('order');
    }






    /**
     * Get videos from media library (regular method, not accessor)
     */
    public function getVideos()
    {
        return $this->getMedia('videos')->map(function ($media) {
            return [
                'id' => $media->id,
                'name' => $media->name,
                'caption' => $media->getCustomProperty('caption', ''),
                'url' => $media->getUrl(),
                'thumbnail' => $this->getVideoThumbnailUrl($media),
                'file_size' => $media->size,
                'mime_type' => $media->mime_type,
                'duration' => $media->getCustomProperty('duration', 0),
                'created_at' => $media->created_at,
            ];
        });
    }
    
    /**
     * Get video thumbnail URL
     */
    public function getVideoThumbnailUrl($media)
    {
        // Check if we have a custom thumbnail path
        $thumbnailPath = $media->getCustomProperty('thumbnail_path');
        if ($thumbnailPath && file_exists(storage_path('app/public/' . $thumbnailPath))) {
            return asset('storage/' . $thumbnailPath);
        }
        
        // Fallback to Media Library conversion
        if ($media->hasGeneratedConversion('thumbnail')) {
            return $media->getUrl('thumbnail');
        }
        
        // Default thumbnail
        return '/default-video-thumbnail.svg';
    }

    /**
     * Get images from media library (regular method, not accessor)
     */
    public function getImages()
    {
        return $this->getMedia('images')->map(function ($media) {
            return [
                'id' => $media->id,
                'name' => $media->name,
                'caption' => $media->getCustomProperty('caption', ''),
                'url' => $media->getUrl(),
                'thumbnail' => $media->getUrl('thumbnail'),
                'file_size' => $media->size,
                'mime_type' => $media->mime_type,
                'created_at' => $media->created_at,
            ];
        });
    }

    /**
     * Get documents from media library (regular method, not accessor)
     */
    public function getDocuments()
    {
        return $this->getMedia('documents')->map(function ($media) {
            return [
                'id' => $media->id,
                'caption' => $media->getCustomProperty('caption', ''),
                'url' => $media->getUrl(),
                'thumbnail' => $media->getUrl('thumbnail'),
                'file_size' => $media->size,
                'mime_type' => $media->mime_type,
                'page_count' => $media->getCustomProperty('page_count', 0),
                'created_at' => $media->created_at,
            ];
        });
    }

    /**
     * Get the password reset code attribute.
     */
    public function getPasswordResetCodeAttribute()
    {
        return $this->attributes['password_reset_code'] ?? null;
    }

    /**
     * Get the password reset code expires at attribute.
     */
    public function getPasswordResetCodeExpiresAtAttribute()
    {
        return $this->attributes['password_reset_code_expires_at'] ?? null;
    }

    /**
     * Get the profile photo URL (regular method, not accessor)
     */
    public function getProfilePhotoUrl()
    {
        // Get the latest profile photo from Spatie Media Library
        $profilePhoto = $this->getMedia('profile-photos')->first();
        
        if ($profilePhoto) {
            return $profilePhoto->getUrl();
        }
        
        // Fallback to Jetstream's profile photo if available
        if ($this->profile_photo_path) {
            return asset('storage/' . $this->profile_photo_path);
        }
        
        return null;
    }

    /**
     * Get the banner URL (regular method, not accessor)
     */
    public function getBannerUrl()
    {
        // Get the latest banner from Spatie Media Library
        $banner = $this->getMedia('banners')->first();
        
        if ($banner) {
            return $banner->getUrl();
        }
        
        // Fallback to old banner path if available
        if ($this->banner_path) {
            return asset('storage/' . $this->banner_path);
        }
        
        return null;
    }

    /**
     * Get user settings with defaults
     */
    public function getSettingsAttribute($value)
    {
        // If settings is already an array, return it
        if (is_array($value)) {
            return $value;
        }
        
        // Decode JSON settings
        $settings = is_string($value) ? json_decode($value, true) : [];
        $settings = is_array($settings) ? $settings : [];
        
        // Merge with defaults
        return array_merge([
            'subscription_plan' => 'Free',
            'privacy_settings' => [
                'profile_visible' => (bool) ($this->attributes['is_public'] ?? false),
                'show_tributes' => false,
                'allow_tribute_requests' => false,
                'email_notifications' => false,
            ],
            'permissions' => [
                'legacy_messages' => true,
                'family_management' => false,
                'ai_suggestions' => false,
            ],
        ], $settings);
    }

    /**
     * Get the preferred identifier for URLs (username if available, otherwise ID)
     */
    public function getPreferredIdentifier(): string
    {
        return $this->username ?: (string) $this->id;
    }

    /**
     * Get the profile URL for this user
     */
    public function getProfileUrl(): string
    {
        return route('profile.show', ['identifier' => $this->getPreferredIdentifier()]);
    }
}
