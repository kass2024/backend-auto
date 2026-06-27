<?php

namespace App\Filament\Support;

use Filament\Notifications\Notification;

class InvoiceFlashNotifications
{
    /**
     * @param  'success'|'danger'|'warning'  $type
     */
    public static function flash(string $type, string $title, string $body): void
    {
        session()->flash('invoice_email_notice', [
            'type' => $type,
            'title' => $title,
            'body' => $body,
        ]);
    }

    public static function showFromSession(): void
    {
        $notice = session()->pull('invoice_email_notice');

        if (! is_array($notice)) {
            return;
        }

        $notification = Notification::make()
            ->title($notice['title'] ?? 'Invoice')
            ->body($notice['body'] ?? '')
            ->duration(12000);

        match ($notice['type'] ?? 'success') {
            'danger' => $notification->danger(),
            'warning' => $notification->warning(),
            default => $notification->success(),
        };

        $notification->send();
    }
}
