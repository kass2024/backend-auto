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

        $showQr = $invoice->showsPaymentQr();
        $qrKey = $showQr ? \App\Support\PaymentMethodDetails::qrKey($invoice->payment_method) : null;

        return [
            'invoice' => $invoice,
            'parts' => $invoice->items->where('type', 'part'),
            'services' => $invoice->items->where('type', 'service'),
            'logoUrl' => $showLogo ? self::logoUrl($forEmail) : '',
            'showLogo' => $showLogo,
            'qrUrl' => $showQr ? self::qrUrl($forEmail, $qrKey) : '',
            'qrPath' => $showQr ? self::qrAbsolutePath($qrKey) : null,
            'qrKey' => $qrKey,
            'embedQr' => false,
            'paymentDetails' => $invoice->paymentMethodDetails(),
            'includeStripeLink' => $includeStripeLink,
            'paymentUrl' => $includeStripeLink ? $invoice->stripe_payment_url : null,
            'pdfUrl' => $forEmail ? self::publicViewUrl($invoice) : null,
            'inlineStyles' => view('invoices.partials.styles')->render(),
        ];
    }

    public static function logoUrl(bool $forEmail = false): string
    {
        $path = config('neamee.logo', 'images/logo/logo.png');

        return $forEmail ? self::emailUrl($path, asset: true) : asset($path);
    }

    public static function qrUrl(bool $forEmail = false, ?string $qrKey = 'zelle'): string
    {
        $path = self::qrPublicPath($qrKey);

        if ($path === null) {
            return '';
        }

        // Emails must not rely on localhost asset URLs — callers should CID-embed when forEmail.
        // Keep a public URL as a last-resort fallback for web/print.
        return $forEmail ? self::emailUrl($path, asset: true) : asset($path);
    }

    public static function qrPublicPath(?string $qrKey = 'zelle'): ?string
    {
        $candidates = match ($qrKey) {
            'cash_app' => ['images/qr-cashapp.png', 'images/qr-cashapp.jpeg'],
            default => ['images/qr-egide.png', 'images/qr-egide.jpeg'],
        };

        foreach ($candidates as $relative) {
            if (is_file(public_path($relative))) {
                return $relative;
            }
        }

        return null;
    }

    public static function qrAbsolutePath(?string $qrKey = 'zelle'): ?string
    {
        $relative = self::qrPublicPath($qrKey);

        return $relative ? public_path($relative) : null;
    }

    public static function publicViewUrl(Invoice $invoice): ?string
    {
        $token = $invoice->public_view_token ?: $invoice->ensurePublicViewToken();

        if (blank($token)) {
            return null;
        }

        return self::emailUrl(
            fn (): string => route('invoice.view', ['invoice' => $invoice->id, 'token' => $token], absolute: true)
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
