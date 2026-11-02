<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\PasswordResetOtp;
use App\Services\EmailOtpService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class PasswordResetOtpController extends Controller
{
    public function send(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);
        $email = Str::lower($validated['email']);
        $rateLimitKey = 'password-reset-otp:'.hash('sha256', $email);

        if (RateLimiter::tooManyAttempts($rateLimitKey, 1)) {
            return back()
                ->withErrors(['email' => 'Please wait before requesting another code.'])
                ->withInput($request->only('email'));
        }

        RateLimiter::hit($rateLimitKey, 60);
        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->where('is_active', true)
            ->whereNotNull('email_verified_at')
            ->first();

        if ($user) {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            Cache::put($this->cacheKey($email), [
                'user_id' => $user->id,
                'code_hash' => Hash::make($code),
            ], now()->addMinutes(EmailOtpService::EXPIRATION_MINUTES));

            $user->notify(new PasswordResetOtp($code));
        } else {
            Cache::forget($this->cacheKey($email));
        }

        $request->session()->put('password_reset_email', $email);

        return back()->with('status', 'If an active account uses that email, a password reset code has been sent.');
    }

    public function reset(Request $request): RedirectResponse
    {
        $email = $request->session()->get('password_reset_email');

        if (! is_string($email)) {
            return to_route('password.request')
                ->withErrors(['email' => 'Request a password reset code first.']);
        }

        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);
        $reset = Cache::get($this->cacheKey($email));

        if (! is_array($reset)
            || ! isset($reset['user_id'], $reset['code_hash'])
            || ! Hash::check($validated['otp'], $reset['code_hash'])) {
            return back()->withErrors(['otp' => 'The code is invalid or has expired. Request a new code and try again.']);
        }

        $user = User::query()
            ->whereKey($reset['user_id'])
            ->whereRaw('LOWER(email) = ?', [$email])
            ->where('is_active', true)
            ->whereNotNull('email_verified_at')
            ->first();

        if (! $user) {
            Cache::forget($this->cacheKey($email));

            return back()->withErrors(['otp' => 'The code is invalid or has expired. Request a new code and try again.']);
        }

        $user->forceFill([
            'password' => $validated['password'],
            'remember_token' => Str::random(60),
        ])->save();

        Cache::forget($this->cacheKey($email));
        $request->session()->forget('password_reset_email');
        RateLimiter::clear('password-reset-otp:'.hash('sha256', $email));
        event(new PasswordReset($user));

        return to_route('login')->with('status', 'Your password has been reset. You can now log in.');
    }

    private function cacheKey(string $email): string
    {
        return 'password-reset-otp:'.hash('sha256', $email);
    }
}
