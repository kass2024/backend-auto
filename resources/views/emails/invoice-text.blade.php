Hello {{ $invoice->user?->name }},

Thank you for choosing {{ config('neamee.company_name') }}.

Invoice: {{ $invoice->invoice_number }}
Date: {{ $invoice->created_at->format('M j, Y') }}
Due: {{ $invoice->due_date?->format('M j, Y') ?? 'Upon receipt' }}
Status: {{ $invoice->isPaid() ? 'PAID' : 'UNPAID' }}
Total: ${{ number_format($invoice->total, 2) }}

@if(!empty($pdfUrl))
View and save PDF:
{{ $pdfUrl }}

@endif
@if(!empty($includeStripeLink) && !empty($paymentUrl))
Pay securely with Stripe:
{{ $paymentUrl }}

@endif
Payment methods: Cash, Check, Bank Transfer, Credit Card (Stripe), Mobile Money.

@if($invoice->paymentMethodLabel() && ! $invoice->isPaid())
Preferred payment method on file: {{ $invoice->paymentMethodLabel() }}
@endif

Questions? Reply to this email or call {{ config('neamee.phone') }}.

Thank you,
{{ config('neamee.company_name') }}
{{ config('neamee.address_line1') }}, {{ config('neamee.address_line2') }}
