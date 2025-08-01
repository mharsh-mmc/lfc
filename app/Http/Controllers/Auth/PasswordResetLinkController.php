<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class PasswordResetLinkController extends Controller
{
    /**
     * Show the password reset link request page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/ForgotPassword', [
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $code = random_int(100000, 999999);
            $user->password_reset_code = $code;
            $user->password_reset_code_expires_at = now()->addMinutes(10);
            $user->save();

            // Send code to email
            Mail::raw("Your password reset code is: $code", function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Your Password Reset Code');
            });
        }

        return back()->with('status', __('A reset code will be sent if the account exists.'));
    }
}
