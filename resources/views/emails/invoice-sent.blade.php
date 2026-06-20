<x-mail::message>
# Invoice {{ $invoice->invoice_number }}

Hello {{ $invoice->user->name }},

Thank you for choosing **NEAMEE Auto-Tech Solutions**. Please find your invoice details below.

**Invoice #:** {{ $invoice->invoice_number }}  
**Date:** {{ $invoice->created_at->format('M j, Y') }}  
**Due date:** {{ $invoice->due_date?->format('M j, Y') ?? 'Upon receipt' }}  
**Status:** {{ ucfirst($invoice->status) }}

<x-mail::table>
| Description | Qty | Unit | Total |
|:------------|----:|-----:|------:|
@foreach($invoice->items as $item)
| {{ $item->description }} | {{ $item->quantity }} | ${{ number_format($item->unit_price, 2) }} | ${{ number_format($item->total, 2) }} |
@endforeach
</x-mail::table>

@if($invoice->discount > 0)
**Discount:** -${{ number_format($invoice->discount, 2) }}
@endif

@if($invoice->tax_amount > 0)
**Tax ({{ $invoice->tax_rate }}%):** ${{ number_format($invoice->tax_amount, 2) }}
@endif

**Total due:** **${{ number_format($invoice->total, 2) }}**

<x-mail::button :url="rtrim(env('FRONTEND_URL', 'https://neamee-autotechsolutions.com'), '/').'/portal/invoices'">
View in Customer Portal
</x-mail::button>

Questions? Reply to this email or call us at **+1 (567) 329-9231**.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
