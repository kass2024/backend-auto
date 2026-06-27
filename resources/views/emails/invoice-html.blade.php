<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>{!! $inlineStyles !!}</style>
</head>
<body class="invoice-document-body" style="padding: 20px 12px 32px; background: {{ config('neamee.brand.page_bg') }};">
    <div class="invoice-email-intro">
        <p>Hello {{ $invoice->user?->name }},</p>
        <p>Thank you for choosing <strong>{{ config('neamee.company_name') }}</strong>. Your invoice <strong>{{ $invoice->invoice_number }}</strong> is below.</p>
        @if(!empty($pdfUrl))
            <p style="text-align:center;">
                <a href="{{ $pdfUrl }}" class="invoice-pdf-cta">View &amp; Save PDF</a>
            </p>
        @endif
        @if(!empty($includeStripeLink) && !empty($paymentUrl))
            <p style="text-align:center;">
                <a href="{{ $paymentUrl }}" class="invoice-stripe-cta">Pay securely with Stripe — ${{ number_format($invoice->total, 2) }}</a>
            </p>
        @endif
    </div>

    @include('invoices.partials.sheet')
</body>
</html>
