<?php

namespace App\Filament\Support;

/** @deprecated Use AdminFlashNotifications */
class InvoiceFlashNotifications
{
    /**
     * @param  'success'|'danger'|'warning'  $type
     */
    public static function flash(string $type, string $title, string $body): void
    {
        AdminFlashNotifications::flash($type, $title, $body);
    }

    public static function showFromSession(): void
    {
        AdminFlashNotifications::showFromSession();
    }
}
