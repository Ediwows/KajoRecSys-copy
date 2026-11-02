<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\EmailOtpService;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse;

class LoginTwoFactorOtpController extends Controller
{
    public function send(Request $request, EmailOtpService $otp): RedirectResponse
    {
        $user = $this->challengedUser($request);

        if (! $user) {
            return to_route('login');
        }

        $otp->send(
            $user,
            'login-two-factor',
            'Your login verification code',
            'Enter this code to finish signing in:',
        );

        return back()
            ->with('status', 'A login code has been sent to your registered email address.')
            ->with('email_otp_sent', true);
    }

    public function verify(
        Request $request,
        EmailOtpService $otp,
        StatefulGuard $guard,
        LoginResponse $loginResponse,
    ): RedirectResponse {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $user = $this->challengedUser($request);

        if (! $user || ! $otp->verify($user, 'login-two-factor', $request->string('otp')->toString())) {
            return back()
                ->withErrors(['otp' => 'The code is invalid or has expired. Request a new code and try again.'])
                ->with('email_otp_sent', true);
        }

        $guard->login($user, $request->session()->pull('login.remember', false));
        $request->session()->forget('login.id');
        $request->session()->regenerate();

        return $loginResponse->toResponse($request);
    }

    private function challengedUser(Request $request): ?User
    {
        $id = $request->session()->get('login.id');
        $user = is_numeric($id) ? User::query()->find($id) : null;

        return $user instanceof User && $user->hasEnabledTwoFactorAuthentication()
            ? $user
            : null;
    }
}
