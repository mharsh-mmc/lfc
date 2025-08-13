<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UsernameRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Check if username is provided
        if (empty($value)) {
            $fail('The username field is required.');
            return;
        }

        // Check length (3-30 characters)
        if (strlen($value) < 3) {
            $fail('The username must be at least 3 characters long.');
            return;
        }

        if (strlen($value) > 30) {
            $fail('The username must not exceed 30 characters.');
            return;
        }

        // Check for valid characters (alphanumeric, hyphens, underscores only)
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $value)) {
            $fail('The username can only contain letters, numbers, hyphens, and underscores.');
            return;
        }

        // Check for reserved usernames
        $reservedUsernames = [
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

        if (in_array(strtolower($value), $reservedUsernames)) {
            $fail('This username is reserved and cannot be used.');
            return;
        }

        // Check for consecutive special characters
        if (preg_match('/[-_]{2,}/', $value)) {
            $fail('The username cannot contain consecutive hyphens or underscores.');
            return;
        }

        // Check if username starts or ends with special characters
        if (preg_match('/^[-_]|[-_]$/', $value)) {
            $fail('The username cannot start or end with hyphens or underscores.');
            return;
        }

        // Check for uniqueness (case-insensitive) - exclude current user for updates
        $currentUserId = request()->user() ? request()->user()->id : null;
        $query = DB::table('users')->whereRaw('LOWER(username) = ?', [strtolower($value)]);
        
        if ($currentUserId) {
            $query->where('id', '!=', $currentUserId);
        }
        
        $existingUser = $query->first();

        if ($existingUser) {
            $fail('This username is already taken.');
            return;
        }
    }
}
