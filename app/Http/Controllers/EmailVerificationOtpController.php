<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\EmailOtpService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailVerificationOtpController extends Controller
{
    public const PENDING_USER_SESSION_KEY = 'pending_email_verification_user_id';

    public function registrationNotice(Request $request): View|RedirectResponse
    {
        if (! $this->pendingUser($request)) {
            return to_route('register');
        }

        return view('pages::auth.verify-email');
    }

    public function send(Request $request): RedirectResponse
    {
        $user = $this->verificationUser($request);

        if (! $user instanceof User) {
            return to_route('login');
        }

        if ($user->hasVerifiedEmail()) {
            return to_route('dashboard');
        }

        $user->sendEmailVerificationNotification();

        return back()->with('status', 'A verification code has been sent to your registered email address.');
    }

    public function verify(Request $request, EmailOtpService $otp): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $user = $this->verificationUser($request);

        if (! $user instanceof User) {
            return to_route('login');
        }

        if ($user->hasVerifiedEmail()) {
            return to_route('dashboard');
        }

        if (! $otp->verify($user, 'email-verification', $request->string('otp')->toString())) {
            return back()
                ->withErrors(['otp' => 'The code is invalid or has expired. Request a new code and try again.'])
                ->with('email_otp_sent', true);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        if ($this->isPendingRegistration($request, $user)) {
            $request->session()->forget(self::PENDING_USER_SESSION_KEY);
            Auth::guard('web')->login($user);
            $request->session()->regenerate();
        }

        return redirect()->to(route('dashboard', absolute: false).'?verified=1');
    }

    private function verificationUser(Request $request): ?User
    {
        $authenticatedUser = $request->user();

        if ($authenticatedUser instanceof User) {
            return $authenticatedUser;
        }

        return $this->pendingUser($request);
    }

    private function pendingUser(Request $request): ?User
    {
        $id = $request->session()->get(self::PENDING_USER_SESSION_KEY);
        $user = is_numeric($id) ? User::query()->find($id) : null;

        return $user instanceof User && ! $user->hasVerifiedEmail() ? $user : null;
    }

    private function isPendingRegistration(Request $request, User $user): bool
    {
        return ! $request->user()
            && (int) $request->session()->get(self::PENDING_USER_SESSION_KEY) === (int) $user->getKey();
    }
}
