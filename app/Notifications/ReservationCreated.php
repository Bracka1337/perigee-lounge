<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ReservationCreated extends Notification
{
    public function __construct(public Reservation $reservation) {}

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
            ->subject(__('reservation-email-received-title').' | '.config('app.name'))
            ->view('emails.reservation-created', [
                'appName' => config('app.name', 'Perigee Lounge'),
                'reservation' => $this->reservation,
                'user' => $notifiable,
            ]);
    }
}
