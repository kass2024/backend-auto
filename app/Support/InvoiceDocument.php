<?php

namespace App\Support;

use App\Models\Invoice;
use Illuminate\Support\Facades\URL;

class InvoiceDocument
{
    /**
     * @return array<string, mixed>
     */
    public static function viewData(
        Invoice $invoice,
        ?bool $includeStripeLink = null,
        bool $showLogo = true,
        bool $includePdfUrl = false,
    ): array {
        $invoice->loadMissing([
            'user',
            'vehicle',
            'jobCard.vehicle',
            'items.part',
            'items.service',
        ]);

        if ($includeStripeLink === null) {
            $includeStripeLink = $invoice->wantsStripePayment() && ! $invoice->isPaid();
        }

        $pdfUrl = $includePdfUrl ? self::signedInvoiceViewUrl($invoice) : null;

        return [
            'invoice' => $invoice,
            'parts' => $invoice->items->where('type', 'part'),
            'services' => $invoice->items->where('type', 'service'),
            'logoUrl' => self::publicAsset(config('neamee.logo')),
            'showLogo' => $showLogo,
            'includeStripeLink' => $includeStripeLink,
            'paymentUrl' => $includeStripeLink ? $invoice->stripe_payment_url : null,
            'pdfUrl' => $pdfUrl,
        ];
    }

    public static function signedInvoiceViewUrl(Invoice $invoice): string
    {
        return self::withPublicRootUrl(
            fn (): string => URL::signedRoute('invoice.view', ['invoice' => $invoice->id])
        );
    }

    public static function publicAsset(string $path): string
    {
        return self::withPublicRootUrl(
            fn (): string => asset($path)
        );
    }

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public static function withPublicRootUrl(callable $callback): mixed
    {
        $publicUrl = rtrim((string) config('neamee.email_app_url', config('app.url')), '/');

        if (str_contains($publicUrl, 'localhost') || str_contains($publicUrl, '127.0.0.1')) {
            throw new \RuntimeException(
                'MAIL_APP_URL must be your public website URL (not localhost) so invoice emails reach customer inboxes. '.
                'Set MAIL_APP_URL=https://api.neamee-autotechsolutions.com in .env'
            );
        }

        $previous = config('app.url');
        URL::forceRootUrl($publicUrl);

        try {
            return $callback();
        } finally {
            URL::forceRootUrl($previous);
        }
    }
}
