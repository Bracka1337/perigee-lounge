<?php

namespace App\Services;

use App\Jobs\SendTelegramMessage;
use App\Models\Order;
use App\Models\Reservation;

/**
 * Builds staff Telegram messages in the staff language (TELEGRAM_LOCALE, else the
 * site default) and queues them. Does nothing until the bot token and chat are set.
 */
class TelegramNotifier
{
    private const RULE = '━━━━━━━━━━━━━━━';

    /**
     * @param  list<int>  $newItemIds
     */
    public static function notifyAddedItems(Order $order, array $newItemIds): void
    {
        $chatId = config('services.telegram.orders_chat_id');
        if (! self::configured($chatId)) {
            return;
        }

        $order->loadMissing(['table', 'items.menuItem', 'user']);

        $lines = [
            '➕ <b>'.self::t('telegram-items-added-title').'</b>',
            '',
            '🪑 <b>'.self::t('telegram-table').':</b> '.e($order->table->name ?? "#{$order->table_id}"),
            '👤 <b>'.self::t('telegram-client').':</b> '.e($order->user->name ?? self::t('telegram-guest')),
            '🆔 <b>'.self::t('telegram-order').':</b> #'.$order->id,
            '',
            '<b>'.self::t('telegram-added').':</b>',
        ];

        foreach ($order->items as $item) {
            if (in_array($item->id, $newItemIds, true)) {
                $lines[] = '• '.e(self::itemName($item->menuItem?->name)).' × '.$item->quantity;
            }
        }

        SendTelegramMessage::dispatch((string) $chatId, implode("\n", $lines));
    }

    public static function notifyNewReservation(Reservation $reservation): void
    {
        $chatId = config('services.telegram.staff_chat_id');
        if (! self::configured($chatId)) {
            return;
        }

        $reservation->loadMissing(['table', 'user']);

        $lines = [
            '🆕 <b>'.self::t('telegram-new-reservation-title').'</b>',
            '',
            self::RULE,
            '📅 <b>'.self::t('telegram-date-time').':</b> '.$reservation->start_at->format('d.m.Y H:i'),
            '🪑 <b>'.self::t('telegram-table').':</b> '.e($reservation->table->name ?? "#{$reservation->table_id}"),
            '👥 <b>'.self::t('telegram-guests').':</b> '.$reservation->guests_count,
            self::RULE,
            '👤 <b>'.self::t('telegram-name').':</b> '.e(self::guestName($reservation)),
            '✉️ <b>Email:</b> '.e($reservation->user->email ?? '—'),
            self::RULE,
            '📊 <b>'.self::t('telegram-status').':</b> '.self::t('staff-reservations-status-'.$reservation->status->value),
            '🆔 <b>'.self::t('telegram-reservation-id').':</b> #'.$reservation->id,
        ];

        SendTelegramMessage::dispatch((string) $chatId, implode("\n", $lines));
    }

    public static function notifyCancelledReservation(Reservation $reservation): void
    {
        $chatId = config('services.telegram.staff_chat_id');
        if (! self::configured($chatId)) {
            return;
        }

        $reservation->loadMissing(['table', 'user']);

        $lines = [
            '🚫 <b>'.self::t('telegram-reservation-cancelled-title').'</b>',
            '',
            self::RULE,
            '📅 <b>'.self::t('telegram-date-time').':</b> '.$reservation->start_at->format('d.m.Y H:i'),
            '🪑 <b>'.self::t('telegram-table').':</b> '.e($reservation->table->name ?? "#{$reservation->table_id}"),
            '👤 <b>'.self::t('telegram-name').':</b> '.e(self::guestName($reservation)),
            self::RULE,
            '🆔 <b>'.self::t('telegram-reservation-id').':</b> #'.$reservation->id,
        ];

        SendTelegramMessage::dispatch((string) $chatId, implode("\n", $lines));
    }

    private static function configured(mixed $chatId): bool
    {
        $token = config('services.telegram.staff_bot_token');

        return is_string($token) && $token !== '' && is_scalar($chatId) && (string) $chatId !== '';
    }

    private static function locale(): string
    {
        $locale = config('services.telegram.locale');
        if (! is_string($locale) || $locale === '') {
            $locale = config('site.default_locale', 'ru');
        }

        return is_string($locale) ? $locale : 'ru';
    }

    private static function t(string $key): string
    {
        return (string) __($key, [], self::locale());
    }

    /**
     * @param  array<string, string>|null  $names
     */
    private static function itemName(?array $names): string
    {
        return $names[self::locale()] ?? $names['en'] ?? '';
    }

    private static function guestName(Reservation $reservation): string
    {
        $name = $reservation->user->name ?? $reservation->guest_name;

        return is_string($name) && $name !== '' ? $name : self::t('telegram-guest');
    }
}
