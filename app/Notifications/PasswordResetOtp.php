<?php

namespace App\Notifications;

use App\Services\EmailOtpService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetOtp extends Notification
{
    use Queueable;

    public function __construct(private readonly string $code) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your password reset code')
            ->greeting('Password reset requested')
            ->line('Use this one-time code to reset your password:')
            ->line($this->code)
            ->line('This code expires in '.EmailOtpService::EXPIRATION_MINUTES.' minutes. If you did not request a password reset, you can ignore this email.')
            ->line('Do not share this code with anyone.');
    }
}
