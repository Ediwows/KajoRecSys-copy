<?php

namespace App\Http\Responses;

use App\Http\Controllers\EmailVerificationOtpController;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use LogicException;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
        $user = $request->user();

        if (! $user instanceof User) {
            throw new LogicException('The newly registered user is not authenticated.');
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->put(EmailVerificationOtpController::PENDING_USER_SESSION_KEY, $user->getKey());

        return redirect()->route('registration.verification.notice')
            ->with('status', 'Your account has been created. Verify your email address to continue.');
    }
}
