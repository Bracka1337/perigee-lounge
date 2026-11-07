<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

/**
 * Posts one message to a staff Telegram chat. Runs on the queue so a slow or
 * failing Telegram API never holds up the guest's request; failures are retried.
 */
class SendTelegramMessage implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [10, 60];

    public function __construct(public string $chatId, public string $text) {}

    public function handle(): void
    {
        $token = config('services.telegram.staff_bot_token');
        if (! is_string($token) || $token === '') {
            return;
        }

        $request = Http::timeout(5);
        if (app()->environment('local')) {
            $request = $request->withoutVerifying();
        }

        $request->post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $this->chatId,
            'text' => $this->text,
            'parse_mode' => 'HTML',
        ])->throw();
    }
}
