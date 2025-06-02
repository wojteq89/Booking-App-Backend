<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPassword extends Notification
{
    public $token;
    public $email;

    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url("/reset-password/{$this->token}?email={$this->email}");

        return (new MailMessage)
            ->subject('Resetowanie hasła')
            ->view('emails.custom-reset-password', [
                'url' => $url,
                'user' => $notifiable,
            ]);
    }
}

