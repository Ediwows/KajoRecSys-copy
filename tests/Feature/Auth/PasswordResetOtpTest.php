<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\PasswordResetOtp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_verified_user_can_reset_password_with_the_email_otp(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('password_reset_email', strtolower($user->email));

        $notification = Notification::sent($user, PasswordResetOtp::class)->first();
        $otp = $notification->toMail($user)->introLines[1];
        $this->assertMatchesRegularExpression('/^\d{6}$/', $otp);

        $this->from(route('password.request'))
            ->post(route('password.update'), [
                'otp' => $otp,
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));
    }

    public function test_password_reset_code_is_not_sent_for_unverified_or_inactive_accounts(): void
    {
        Notification::fake();
        $unverified = User::factory()->unverified()->create();
        $inactive = User::factory()->create(['is_active' => false]);

        foreach ([$unverified, $inactive] as $user) {
            $this->post(route('password.email'), ['email' => $user->email])
                ->assertSessionHas('password_reset_email', strtolower($user->email));
        }

        Notification::assertNothingSent();
    }

    public function test_password_reset_request_does_not_disclose_unknown_accounts(): void
    {
        Notification::fake();

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => 'unknown@example.com'])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('status', 'If an active account uses that email, a password reset code has been sent.')
            ->assertSessionHas('password_reset_email', 'unknown@example.com');

        Notification::assertNothingSent();
    }

    public function test_expired_otp_cannot_reset_password(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email]);
        $this->travel(31)->minutes();

        $this->from(route('password.request'))
            ->post(route('password.update'), [
                'otp' => '123456',
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors('otp');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_password_reset_code_remains_valid_after_ten_minutes(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email]);
        $notification = Notification::sent($user, PasswordResetOtp::class)->first();
        $otp = $notification->toMail($user)->introLines[1];
        $this->travel(11)->minutes();

        $this->from(route('password.request'))
            ->post(route('password.update'), [
                'otp' => $otp,
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));
    }

    public function test_invalid_otp_can_be_retried_without_changing_the_password(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email]);
        $notification = Notification::sent($user, PasswordResetOtp::class)->first();
        $validOtp = $notification->toMail($user)->introLines[1];
        $invalidOtp = str_pad((string) (((int) $validOtp + 1) % 1000000), 6, '0', STR_PAD_LEFT);

        $this->from(route('password.request'))
            ->post(route('password.update'), [
                'otp' => $invalidOtp,
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors('otp');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));

        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('name="otp"', false);

        $this->from(route('password.request'))
            ->post(route('password.update'), [
                'otp' => $validOtp,
                'password' => 'new-secure-password',
                'password_confirmation' => 'new-secure-password',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-secure-password', $user->fresh()->password));
    }
}
