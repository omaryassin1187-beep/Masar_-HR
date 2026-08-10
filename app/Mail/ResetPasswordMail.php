<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $token,
    ) {}

    public function build()
    {
        return $this
            ->subject('Reset Your Password')
            ->view('emails.reset_password')
            ->with([
                'user' => $this->user,
                'token' => $this->token,
                'title' => 'Reset Password',
            ]);
    }
}
