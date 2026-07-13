<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class WelcomeEmployeeNotification extends Notification
{
    use Queueable;

    public function __construct(
        private string $email
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $setPasswordUrl = URL::temporarySignedRoute(
            'password.set',
            now()->addDays(7),
            ['email' => $this->email]
        );

        return (new MailMessage)
            ->subject('Welcome to ' . config('app.name'))
            ->view('emails.welcome', [
                'userName' => $notifiable->full_name,
                'email' => $notifiable->email,
                'setPasswordUrl' => $setPasswordUrl,
            ]);
    }
}
