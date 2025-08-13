<?php

namespace App\Services;

use App\Models\User;

class ProfileUrlService
{
    /**
     * Get the preferred identifier for a user (username if available, otherwise ID)
     */
    public function getPreferredIdentifier(User $user): string
    {
        return $user->username ?: (string) $user->id;
    }

    /**
     * Get the profile URL for a user
     */
    public function getProfileUrl(User $user): string
    {
        return route('profile.show', ['identifier' => $this->getPreferredIdentifier($user)]);
    }

    /**
     * Get the profile URL for a user with a specific tab
     */
    public function getProfileTabUrl(User $user, string $tab): string
    {
        return route("profile.{$tab}", ['identifier' => $this->getPreferredIdentifier($user)]);
    }

    /**
     * Check if a URL should redirect to username-based URL
     */
    public function shouldRedirectToUsername(string $identifier): bool
    {
        if (!is_numeric($identifier)) {
            return false;
        }

        $user = User::find($identifier);
        return $user && $user->username;
    }

    /**
     * Get the username-based URL for a user ID
     */
    public function getUsernameUrl(string $identifier): ?string
    {
        if (!is_numeric($identifier)) {
            return null;
        }

        $user = User::find($identifier);
        if (!$user || !$user->username) {
            return null;
        }

        return route('profile.show', ['identifier' => $user->username]);
    }

    /**
     * Generate a list of all possible URLs for a user (for sitemap, etc.)
     */
    public function getAllProfileUrls(User $user): array
    {
        $identifier = $this->getPreferredIdentifier($user);
        
        return [
            'main' => route('profile.show', ['identifier' => $identifier]),
            'family' => route('profile.family', ['identifier' => $identifier]),
            'video' => route('profile.video', ['identifier' => $identifier]),
            'letters' => route('profile.letters', ['identifier' => $identifier]),
            'photos' => route('profile.photos', ['identifier' => $identifier]),
        ];
    }
} 