<?php

namespace App\Services;

use App\Enums\ReservationStatus;
use App\Models\Order;
use App\Models\Reservation;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotifier
{
    public static function notifyNewOrder(Order $order): void
    {
        $token = config('services.telegram.staff_bot_token');
        $chatId = config('services.telegram.orders_chat_id');

        if ($token === null || $token === '' || $chatId === null || $chatId === '') {
            Log::warning('TelegramNotifier: missing token or chat_id for new order.');

            return;
        }

        $order->loadMissing(['table', 'items.menuItem', 'user']);

        $tableName = $order->table !== null ? $order->table->name : 'Стол';
        $clientName = $order->user !== null ? ($order->user->name ?? 'Клиент') : 'Гость';

        $text = "🛎️ <b>НОВЫЕ ПОЗИЦИИ В ЗАКАЗЕ</b>\n\n";
        $text .= "🪑 <b>Стол:</b> {$tableName}\n";
        $text .= "👤 <b>Клиент:</b> {$clientName}\n";
        $text .= "🆔 <b>Заказ:</b> #{$order->id}\n";

        $text .= "\n<b>Позиции:</b>\n";
        foreach ($order->items as $item) {
            $name = $item->menuItem->name[app()->getLocale()] ?? ($item->menuItem->name['ru'] ?? 'Позиция');
            $text .= "• {$name} × {$item->quantity}\n";
        }

        $request = Http::timeout(5);
        if (app()->environment('local')) {
            $request = $request->withoutVerifying();
        }

        try {
            $response = $request->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);

            if ($response->successful()) {
                Log::info('TelegramNotifier: order message sent successfully.', [
                    'chat_id' => $chatId,
                    'order_id' => $order->id,
                    'telegram_response' => $response->json(),
                ]);
            } else {
                Log::error('TelegramNotifier: failed to send order message.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'chat_id' => $chatId,
                    'order_id' => $order->id,
                ]);
            }
        } catch (ConnectionException $e) {
            Log::error('TelegramNotifier: connection error when sending order message.', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    public static function notifyAddedItems(Order $order, array $newItemIds): void
    {
        $token = config('services.telegram.staff_bot_token');
        $chatId = config('services.telegram.orders_chat_id');

        if ($token === null || $token === '' || $chatId === null || $chatId === '') {
            return;
        }

        $order->loadMissing(['table', 'items.menuItem', 'user']);

        $tableName = $order->table !== null ? $order->table->name : 'Стол';
        $clientName = $order->user !== null ? ($order->user->name ?? 'Клиент') : 'Гость';

        $text = "➕ <b>НОВЫЕ ПОЗИЦИИ ДОБАВЛЕНЫ</b>\n\n";
        $text .= "🪑 <b>Стол:</b> {$tableName}\n";
        $text .= "👤 <b>Клиент:</b> {$clientName}\n";
        $text .= "🆔 <b>Заказ:</b> #{$order->id}\n";

        $text .= "\n<b>Добавлены:</b>\n";
        foreach ($order->items as $item) {
            if (in_array($item->id, $newItemIds, true)) {
                $name = $item->menuItem->name[app()->getLocale()] ?? ($item->menuItem->name['ru'] ?? 'Позиция');
                $text .= "• {$name} × {$item->quantity}\n";
            }
        }

        $request = Http::timeout(5);
        if (app()->environment('local')) {
            $request = $request->withoutVerifying();
        }

        try {
            $response = $request->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);

            if ($response->successful()) {
                Log::info('TelegramNotifier: added items message sent successfully.', [
                    'chat_id' => $chatId,
                    'order_id' => $order->id,
                ]);
            }
        } catch (ConnectionException $e) {
            Log::error('TelegramNotifier: connection error when sending added items message.', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    public static function notifyNewReservation(Reservation $reservation): void
    {
        $token = config('services.telegram.staff_bot_token');
        $chatId = config('services.telegram.staff_chat_id');

        if ($token === null || $token === '' || $chatId === null || $chatId === '') {
            Log::warning('TelegramNotifier: missing token or chat_id in config.', [
                'token_present' => $token !== null && $token !== '',
                'chat_id_present' => $chatId !== null && $chatId !== '',
            ]);

            return;
        }

        $tableName = $reservation->table !== null ? $reservation->table->name : 'Стол';
        $date = $reservation->start_at !== null ? $reservation->start_at->format('d.m.Y H:i') : '';
        $guestName = $reservation->user !== null
            ? $reservation->user->name
            : (($reservation->guest_name !== null && $reservation->guest_name !== '') ? $reservation->guest_name : 'Гость');
        $guestsCount = $reservation->guests_count;
        $phone = $reservation->user !== null
            ? $reservation->user->phone
            : (($reservation->guest_phone !== null && $reservation->guest_phone !== '') ? $reservation->guest_phone : '—');
        $email = $reservation->user !== null ? $reservation->user->email : '—';
        $status = match ($reservation->status) {
            ReservationStatus::Pending => '⏳ Ожидает',
            ReservationStatus::Active => '✅ Активна',
            ReservationStatus::Completed => '✔️ Завершена',
            ReservationStatus::Cancelled => '❌ Отменена',
            default => $reservation->status->value,
        };

        $text = "🆕 <b>НОВАЯ БРОНЬ</b>\n\n";
        $text .= "━━━━━━━━━━━━━━━\n";
        $text .= "📅 <b>Дата и время:</b>\n";
        $text .= "   {$date}\n\n";
        $text .= "🪑 <b>Стол:</b> {$tableName}\n";
        $text .= "👥 <b>Гостей:</b> {$guestsCount}\n\n";
        $text .= "━━━━━━━━━━━━━━━\n";
        $text .= "👤 <b>Контакты гостя:</b>\n";
        $text .= "   <b>Имя:</b> {$guestName}\n";
        $text .= "   <b>Email:</b> {$email}\n\n";
        $text .= "━━━━━━━━━━━━━━━\n";
        $text .= "📊 <b>Статус:</b> {$status}\n";
        $text .= "🆔 <b>ID брони:</b> #{$reservation->id}";

        $request = Http::timeout(5);

        if (app()->environment('local')) {
            $request = $request->withoutVerifying();
        }

        try {
            $response = $request->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);

            if ($response->successful()) {
                Log::info('TelegramNotifier: message sent successfully.', [
                    'chat_id' => $chatId,
                    'reservation_id' => $reservation->id,
                    'telegram_response' => $response->json(),
                ]);
            } else {
                Log::error('TelegramNotifier: failed to send message to Telegram.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'chat_id' => $chatId,
                    'reservation_id' => $reservation->id,
                ]);
            }
        } catch (ConnectionException $e) {
            Log::error('TelegramNotifier: connection error when sending message.', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    public static function notifyCancelledReservation(Reservation $reservation): void
    {
        $token = config('services.telegram.staff_bot_token');
        $chatId = config('services.telegram.staff_chat_id');

        if ($token === null || $token === '' || $chatId === null || $chatId === '') {
            Log::warning('TelegramNotifier: missing token or chat_id in config (cancel).', [
                'token_present' => $token !== null && $token !== '',
                'chat_id_present' => $chatId !== null && $chatId !== '',
            ]);

            return;
        }

        $tableName = $reservation->table !== null ? $reservation->table->name : 'Стол';
        $date = $reservation->start_at !== null ? $reservation->start_at->format('d.m.Y H:i') : '';
        $guestName = $reservation->user !== null
            ? $reservation->user->name
            : (($reservation->guest_name !== null && $reservation->guest_name !== '') ? $reservation->guest_name : 'Гость');
        $phone = $reservation->user !== null
            ? $reservation->user->phone
            : (($reservation->guest_phone !== null && $reservation->guest_phone !== '') ? $reservation->guest_phone : '—');
        $cancelReason = ($reservation->cancellation_reason !== null && $reservation->cancellation_reason !== '')
            ? $reservation->cancellation_reason
            : 'Не указана';

        $text = "🚫 <b>БРОНЬ ОТМЕНЕНА</b>\n\n";
        $text .= "━━━━━━━━━━━━━━━\n";
        $text .= "📅 <b>Дата и время:</b>\n";
        $text .= "   {$date}\n\n";
        $text .= "🪑 <b>Стол:</b> {$tableName}\n";
        $text .= "👤 <b>Гость:</b> {$guestName}\n";
        $text .= "━━━━━━━━━━━━━━━\n";
        $text .= "🆔 <b>ID брони:</b> #{$reservation->id}";

        $request = Http::timeout(5);

        if (app()->environment('local')) {
            $request = $request->withoutVerifying();
        }

        try {
            $response = $request->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'HTML',
            ]);

            if ($response->successful()) {
                Log::info('TelegramNotifier: cancel message sent successfully.', [
                    'chat_id' => $chatId,
                    'reservation_id' => $reservation->id,
                    'telegram_response' => $response->json(),
                ]);
            } else {
                Log::error('TelegramNotifier: failed to send cancel message.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'chat_id' => $chatId,
                    'reservation_id' => $reservation->id,
                ]);
            }
        } catch (ConnectionException $e) {
            Log::error('TelegramNotifier: connection error on cancel.', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
