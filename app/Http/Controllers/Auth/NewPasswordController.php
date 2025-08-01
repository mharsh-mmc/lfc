<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;

class NewPasswordController extends Controller
{
    /**
     * Show the password reset page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/ResetPassword', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]);
    }

    /**
     * Handle an incoming code verification request.
     */
    public function verifyCode(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)->first();
        if (! $user || $user->password_reset_code !== $request->code || !$user->password_reset_code_expires_at || now()->gt($user->password_reset_code_expires_at)) {
            return back()->withErrors(['code' => 'Invalid or expired code.']);
        }

        // Mark code as verified in session
        session(['password_reset_verified_email' => $user->email]);
        return redirect()->route('password.reset.form');
    }

    /**
     * Show the reset password form after code verification.
     */
    public function showResetForm(Request $request)
    {
        $email = session('password_reset_verified_email');
        if (! $email) {
            return redirect()->route('password.request');
        }
        return Inertia::render('auth/ResetPassword', [
            'email' => $email,
        ]);
    }

    /**
     * Handle an incoming new password request (after code verification).
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = session('password_reset_verified_email');
        if (! $email || $email !== $request->email) {
            return back()->withErrors(['email' => 'Session expired or invalid.']);
        }

        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        $user->password = Hash::make($request->password);
        $user->password_reset_code = null;
        $user->password_reset_code_expires_at = null;
        $user->save();

        session()->forget('password_reset_verified_email');
        event(new PasswordReset($user));
        return to_route('login')->with('status', __('Password reset successful.'));
    }
}
