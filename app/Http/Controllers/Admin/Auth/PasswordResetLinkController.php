<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Reset-link requests allowed per email address and IP each minute.
     */
    private const MAX_REQUESTS_PER_MINUTE = 3;

    public function create(): View
    {
        return view('admin.auth.forgot-password');
    }

    /**
     * Send a reset link to an active account. The response is the same whether or not the email exists.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $throttleKey = 'admin-password-reset|'.Str::transliterate(Str::lower($request->string('email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_REQUESTS_PER_MINUTE)) {
            throw ValidationException::withMessages([
                'email' => 'Too many reset requests. Please try again in '.RateLimiter::availableIn($throttleKey).' seconds.',
            ]);
        }

        RateLimiter::hit($throttleKey);

        Password::sendResetLink([
            'email' => $request->string('email')->toString(),
            'is_active' => true,
        ]);

        return back()->with('status', 'If an account exists for that email address, we have sent a password reset link to it.');
    }
}
