<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCompleted extends Notification
{
    public function __construct(public Order $order) {}

    /**
     * @param  mixed  $notifiable
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * @param  mixed  $notifiable
     */
    public function toMail($notifiable): MailMessage
    {
        $locale = $notifiable->preferredLocale() ?? app()->getLocale();
        app()->setLocale($locale);

        return (new MailMessage)
            ->subject(__('order-email-title').' | '.config('app.name'))
            ->view('emails.order-completed', [
                'appName' => config('app.name', 'Perigee Lounge'),
                'order' => $this->order,
                'user' => $notifiable,
            ]);
    }
}
