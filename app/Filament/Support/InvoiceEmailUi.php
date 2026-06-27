<?php

namespace App\Filament\Support;

class InvoiceEmailUi
{
    public static function actionLabel(bool $wasEmailed): string
    {
        return $wasEmailed ? 'Resend' : 'Email customer';
    }

    public static function modalHeading(bool $wasEmailed): string
    {
        return $wasEmailed ? 'Resend invoice email' : 'Email invoice to customer';
    }

    public static function confirmMessage(string $email, bool $wasEmailed): string
    {
        return $wasEmailed
            ? 'Resend this invoice to '.$email.'?'
            : 'Email this invoice to '.$email.'?';
    }

    public static function successTitle(bool $wasResend): string
    {
        return $wasResend ? 'Invoice resent successfully' : 'Invoice email sent successfully';
    }

    public static function successBody(string $invoiceNumber, string $email, bool $includeStripe): string
    {
        $stripe = $includeStripe
            ? ' Stripe payment link included.'
            : ' No Stripe link.';

        return 'Invoice '.$invoiceNumber.' was sent to '.$email.'.'.$stripe.' Check inbox and spam folder.';
    }
}
