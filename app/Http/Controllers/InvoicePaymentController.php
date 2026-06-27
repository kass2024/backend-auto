<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\StripePaymentService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoicePaymentController extends Controller
{
    public function pay(Invoice $invoice, StripePaymentService $stripe): View
    {
        $invoice->load(['user', 'items', 'vehicle']);

        $paymentUrl = $stripe->refreshCheckoutSession($invoice);

        return view('invoices.pay', [
            'invoice' => $invoice,
            'paymentUrl' => $paymentUrl,
            'stripeConfigured' => $stripe->isConfigured(),
        ]);
    }

    public function success(Request $request, Invoice $invoice, StripePaymentService $stripe)
    {
        if ($sessionId = $request->query('session_id')) {
            $stripe->markPaidFromSessionId($sessionId);
            $invoice->refresh();
        }

        return view('invoices.payment-success', [
            'invoice' => $invoice->fresh(['user']),
        ]);
    }

    public function cancel(Invoice $invoice): View
    {
        return view('invoices.payment-cancel', [
            'invoice' => $invoice->load('user'),
        ]);
    }

    public function webhook(Request $request, StripePaymentService $stripe)
    {
        $handled = $stripe->handleWebhook(
            $request->getContent(),
            $request->header('Stripe-Signature'),
        );

        return response()->json(['received' => $handled], $handled ? 200 : 400);
    }
}
