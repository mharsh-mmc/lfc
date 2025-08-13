<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeceasedProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by',
        'name',
        'birth_date',
        'death_date',
        'birth_place',
        'death_place',
        'biography',
        'memorial_message',
        'relationship',
        'is_public',
        'profile_photo_path',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'death_date' => 'date',
        'is_public' => 'boolean',
    ];

    /**
     * Get the user who created this profile
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the age at death
     */
    public function getAgeAtDeathAttribute()
    {
        if ($this->birth_date instanceof Carbon && $this->death_date instanceof Carbon) {
            return $this->birth_date->diffInYears($this->death_date);
        }
        return null;
    }

    /**
     * Get the years lived string
     */
    public function getYearsLivedAttribute()
    {
        if ($this->birth_date instanceof Carbon && $this->death_date instanceof Carbon) {
            $years = $this->birth_date->diffInYears($this->death_date);
            return $years . ' years';
        }
        return null;
    }

    /**
     * Scope for public profiles
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for profiles by creator
     */
    public function scopeByCreator($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Get the profile photo URL
     */
    public function getProfilePhotoUrlAttribute()
    {
        if ($this->profile_photo_path) {
            return asset('storage/' . $this->profile_photo_path);
        }
        return null;
    }
}
