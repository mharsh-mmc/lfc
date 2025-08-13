<?php

namespace App\Http\Controllers;

use App\Rules\UsernameRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                new UsernameRule,
                'unique:users,username,' . $user->id,
            ],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'subscription_plan' => ['required', 'string', 'max:255'],
            'privacy_settings.profile_visible' => ['boolean'],
            'privacy_settings.show_tributes' => ['boolean'],
            'privacy_settings.allow_tribute_requests' => ['boolean'],
            'privacy_settings.email_notifications' => ['boolean'],
            'permissions.legacy_messages' => ['boolean'],
            'permissions.family_management' => ['boolean'],
            'permissions.ai_suggestions' => ['boolean'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Update user basic information
        $user->update([
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            // Update profile visibility based on settings
            'is_public' => $request->input('privacy_settings.profile_visible', false),
        ]);

        // Update user settings
        $settings = [
            'subscription_plan' => $request->input('subscription_plan'),
            'privacy_settings' => $request->input('privacy_settings'),
            'permissions' => $request->input('permissions'),
        ];

        $user->update([
            'settings' => $settings,
        ]);

        return redirect()->route('settings')->with('success', 'Settings updated successfully.');
    }
} 