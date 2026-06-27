<?php

namespace App\Support;

use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
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
        bool $forEmail = false,
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

        return [
            'invoice' => $invoice,
            'parts' => $invoice->items->where('type', 'part'),
            'services' => $invoice->items->where('type', 'service'),
            'logoUrl' => $showLogo ? self::logoUrl($forEmail) : '',
            'showLogo' => $showLogo,
            'includeStripeLink' => $includeStripeLink,
            'paymentUrl' => $includeStripeLink ? $invoice->stripe_payment_url : null,
            'pdfUrl' => $forEmail ? self::signedInvoiceViewUrl($invoice) : null,
            'inlineStyles' => view('invoices.partials.styles')->render(),
        ];
    }

    public static function logoUrl(bool $forEmail = false): string
    {
        $path = config('neamee.logo', 'images/logo/logo.png');

        return $forEmail ? self::emailUrl($path, asset: true) : asset($path);
    }

    public static function signedInvoiceViewUrl(Invoice $invoice): string
    {
        return self::emailUrl(
            fn (): string => URL::signedRoute('invoice.view', ['invoice' => $invoice->id])
        );
    }

    /**
     * @param  string|(callable(): string)  $pathOrCallback
     */
    public static function emailUrl(string|callable $pathOrCallback, bool $asset = false): string
    {
        return self::withEmailRootUrl(function () use ($pathOrCallback, $asset): string {
            if (is_callable($pathOrCallback)) {
                return $pathOrCallback();
            }

            return $asset ? asset($pathOrCallback) : $pathOrCallback;
        });
    }

    /**
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    public static function withEmailRootUrl(callable $callback): mixed
    {
        $publicUrl = rtrim((string) config('neamee.email_app_url', config('app.url')), '/');

        if (
            $publicUrl === ''
            || str_contains($publicUrl, 'localhost')
            || str_contains($publicUrl, '127.0.0.1')
        ) {
            $publicUrl = rtrim((string) config('app.url'), '/');

            if (
                $publicUrl === ''
                || str_contains($publicUrl, 'localhost')
                || str_contains($publicUrl, '127.0.0.1')
            ) {
                Log::warning('Invoice email links use localhost — set MAIL_APP_URL in .env for production deliverability.');
            }
        }

        $previousRoot = config('app.url');
        URL::forceRootUrl($publicUrl);

        try {
            return $callback();
        } finally {
            URL::forceRootUrl($previousRoot);
        }
    }
}
