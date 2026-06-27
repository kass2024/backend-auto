<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Webhook;

class StripePaymentService
{
    public function isConfigured(): bool
    {
        return filled(config('stripe.secret'));
    }

    public function createCheckoutSession(Invoice $invoice): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $invoice->loadMissing(['user', 'items']);

        if ($invoice->total <= 0 || $invoice->status === 'paid') {
            return null;
        }

        Stripe::setApiKey(config('stripe.secret'));

        $session = Session::create([
            'mode' => 'payment',
            'customer_email' => $invoice->user?->email,
            'line_items' => [[
                'price_data' => [
                    'currency' => config('stripe.currency', 'usd'),
                    'product_data' => [
                        'name' => 'Invoice '.$invoice->invoice_number,
                        'description' => 'NEAMEE Auto-Tech Solutions — automotive repair invoice',
                    ],
                    'unit_amount' => (int) round((float) $invoice->total * 100),
                ],
                'quantity' => 1,
            ]],
            'metadata' => [
                'invoice_id' => (string) $invoice->id,
                'invoice_number' => $invoice->invoice_number,
            ],
            'success_url' => route('invoice.payment.success', $invoice).'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('invoice.payment.cancel', $invoice),
        ]);

        $invoice->update([
            'stripe_checkout_session_id' => $session->id,
            'stripe_payment_url' => $session->url,
        ]);

        return $session->url;
    }

    public function refreshCheckoutSession(Invoice $invoice): ?string
    {
        if ($invoice->status === 'paid') {
            return null;
        }

        if (filled($invoice->stripe_payment_url) && filled($invoice->stripe_checkout_session_id)) {
            return $invoice->stripe_payment_url;
        }

        return $this->createCheckoutSession($invoice);
    }

    public function markPaidFromSessionId(string $sessionId): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        Stripe::setApiKey(config('stripe.secret'));

        try {
            $session = Session::retrieve($sessionId);

            if ($session->payment_status !== 'paid') {
                return false;
            }

            $invoiceId = $session->metadata->invoice_id ?? null;

            if (! $invoiceId) {
                return false;
            }

            $invoice = Invoice::find($invoiceId);

            if (! $invoice || $invoice->status === 'paid') {
                return (bool) $invoice;
            }

            $invoice->update([
                'status' => 'paid',
                'payment_method' => 'credit_card',
                'paid_at' => now(),
                'stripe_checkout_session_id' => $session->id,
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Stripe session verification failed: '.$e->getMessage());

            return false;
        }
    }

    public function handleWebhook(string $payload, ?string $signature): bool
    {
        $secret = config('stripe.webhook_secret');

        if (! filled($secret) || ! filled($signature)) {
            return false;
        }

        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);
        } catch (\Throwable $e) {
            Log::warning('Stripe webhook rejected: '.$e->getMessage());

            return false;
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            return $this->markPaidFromSessionId($session->id);
        }

        return true;
    }
}
