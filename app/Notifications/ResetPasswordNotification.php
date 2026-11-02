<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    public function __construct(public string $token) {}

    /**
     * @param mixed $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * @param mixed $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        $locale = $notifiable->preferredLocale() ?? app()->getLocale();
        app()->setLocale($locale);
        
        return (new MailMessage)
            ->subject(__('reset-password-email-title').' | '.config('app.name'))
            ->view('emails.reset-password', [
                'appName' => config('app.name', 'Perigee Lounge'),
                'url' => route('password.reset', [
                    'locale' => $locale,
                    'token' => $this->token,
                    'email' => $notifiable->email,
                ]),
            ]);
    }
}
