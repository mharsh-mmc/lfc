<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class UsernameService
{
    /**
     * Generate a unique username from a name
     */
    public function generateFromName(string $name): string
    {
        $baseUsername = $this->cleanName($name);
        $username = $baseUsername;
        $counter = 1;

        while ($this->isUsernameTaken($username)) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Clean a name to make it username-friendly
     */
    private function cleanName(string $name): string
    {
        // Remove special characters and convert to lowercase
        $cleaned = preg_replace('/[^a-zA-Z0-9]/', '', strtolower($name));
        
        // Remove consecutive characters
        $cleaned = preg_replace('/(.)\1+/', '$1', $cleaned);
        
        // Limit length
        return substr($cleaned, 0, 20);
    }

    /**
     * Check if a username is already taken
     */
    public function isUsernameTaken(string $username): bool
    {
        return User::whereRaw('LOWER(username) = ?', [strtolower($username)])->exists();
    }

    /**
     * Validate username format
     */
    public function validateFormat(string $username): array
    {
        $errors = [];

        if (strlen($username) < 3) {
            $errors[] = 'Username must be at least 3 characters long.';
        }

        if (strlen($username) > 30) {
            $errors[] = 'Username must not exceed 30 characters.';
        }

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
            $errors[] = 'Username can only contain letters, numbers, hyphens, and underscores.';
        }

        if (preg_match('/[-_]{2,}/', $username)) {
            $errors[] = 'Username cannot contain consecutive hyphens or underscores.';
        }

        if (preg_match('/^[-_]|[-_]$/', $username)) {
            $errors[] = 'Username cannot start or end with hyphens or underscores.';
        }

        return $errors;
    }

    /**
     * Get reserved usernames
     */
    public function getReservedUsernames(): array
    {
        return [
            'admin', 'administrator', 'mod', 'moderator', 'support', 'help', 'info',
            'contact', 'about', 'terms', 'privacy', 'login', 'logout', 'register',
            'password', 'reset', 'email', 'profile', 'settings', 'dashboard',
            'api', 'api-docs', 'docs', 'documentation', 'blog', 'news', 'forum',
            'search', 'explore', 'discover', 'home', 'index', 'main', 'root',
            'www', 'web', 'site', 'app', 'application', 'system', 'server',
            'test', 'demo', 'example', 'sample', 'temp', 'tmp', 'cache',
            'static', 'assets', 'images', 'css', 'js', 'fonts', 'media',
            'uploads', 'downloads', 'files', 'data', 'backup', 'archive',
            'liveforever', 'live', 'forever', 'family', 'tree', 'memorial',
            'tribute', 'memory', 'legacy', 'heritage', 'ancestry', 'genealogy'
        ];
    }

    /**
     * Check if username is reserved
     */
    public function isReserved(string $username): bool
    {
        return in_array(strtolower($username), $this->getReservedUsernames());
    }
} 