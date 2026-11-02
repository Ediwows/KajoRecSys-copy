<?php

namespace App\Notifications;

use App\Services\EmailOtpService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailOtp extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $code,
        private readonly string $subject,
        private readonly string $purpose,
    ) {}

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
            ->subject($this->subject)
            ->greeting('Hello '.$notifiable->name)
            ->line($this->purpose)
            ->line('Your one-time code is: '.$this->code)
            ->line('This code expires in '.EmailOtpService::EXPIRATION_MINUTES.' minutes. Do not share it with anyone.');
    }
}
