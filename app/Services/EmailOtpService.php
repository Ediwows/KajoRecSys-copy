<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\EmailOtp;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmailOtpService
{
    public const EXPIRATION_MINUTES = 30;

    public function send(User $user, string $purpose, string $subject, string $message): void
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Cache::put($this->cacheKey($user, $purpose), [
            'code_hash' => Hash::make($code),
        ], now()->addMinutes(self::EXPIRATION_MINUTES));

        $user->notify(new EmailOtp($code, $subject, $message));
    }

    public function verify(User $user, string $purpose, string $code): bool
    {
        $key = $this->cacheKey($user, $purpose);
        $otp = Cache::get($key);

        if (! is_array($otp)
            || ! isset($otp['code_hash'])
            || ! is_string($otp['code_hash'])
            || ! Hash::check($code, $otp['code_hash'])) {
            return false;
        }

        Cache::forget($key);

        return true;
    }

    private function cacheKey(User $user, string $purpose): string
    {
        return 'email-otp:'.Str::slug($purpose).':'.$user->getKey();
    }
}
