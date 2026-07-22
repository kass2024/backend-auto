Hello {{ $quotation->customer_name }},

Your repair quote {{ $quotation->quote_number }} from {{ config('neamee.company_name') }} is ready.

Total due: ${{ number_format($quotation->total, 2) }}
Expires: {{ $quotation->expires_at?->format('M j, Y') ?? '—' }}

Review and e-sign here:
{{ $quoteUrl }}

Open the link on your phone to confirm before work begins.

{{ config('neamee.company_name') }}
{{ config('neamee.phone') }}
