<x-mail::message>
# Invoice {{ $invoice->invoice_number }}

Hello {{ $invoice->user->name }},

Thank you for choosing **NEAMEE Auto-Tech Solutions**. Your repair invoice is ready.

**Invoice #:** {{ $invoice->invoice_number }}  
**Date:** {{ $invoice->created_at->format('M j, Y') }}  
**Due date:** {{ $invoice->due_date?->format('M j, Y') ?? 'Upon receipt' }}  
**Status:** {{ $invoice->isPaid() ? '**PAID**' : '**UNPAID**' }}

@if($vehicle = $invoice->vehicle ?? $invoice->jobCard?->vehicle)
**Vehicle:** {{ trim("{$vehicle->year} {$vehicle->make} {$vehicle->model}") }} — Plate {{ $vehicle->plate_number }}
@endif

---

## Parts used

@if($invoice->partItems->isNotEmpty())
<x-mail::table>
| Qty | Part no. | Part name | Amount |
|:--:|:--|:--|--:|
@foreach($invoice->partItems as $item)
| {{ $item->quantity }} | {{ $item->part_number ?? '—' }} | {{ $item->description }} | ${{ number_format($item->total, 2) }} |
@endforeach
</x-mail::table>
**Parts total:** ${{ number_format($invoice->parts_total, 2) }}
@else
No parts listed on this invoice.
@endif

## Labor

@if($invoice->serviceItems->isNotEmpty())
<x-mail::table>
| Description | Qty | Amount |
|:--|--:|--:|
@foreach($invoice->serviceItems as $item)
| {{ $item->description }} | {{ $item->quantity }} | ${{ number_format($item->total, 2) }} |
@endforeach
</x-mail::table>
**Labor:** ${{ number_format($invoice->labor_total, 2) }}
@else
No labor listed on this invoice.
@endif

@if($invoice->work_description)
**Description of work:**  
{{ $invoice->work_description }}
@endif

---

@if($invoice->discount > 0)
**Discount:** -${{ number_format($invoice->discount, 2) }}
@endif

@if($invoice->tax_amount > 0)
**Tax ({{ $invoice->tax_rate }}%):** ${{ number_format($invoice->tax_amount, 2) }}
@endif

**TOTAL DUE:** **${{ number_format($invoice->total, 2) }}**

@if(! $invoice->isPaid() && $paymentUrl)
<x-mail::button :url="$paymentUrl">
Pay securely with Stripe
</x-mail::button>

You can also pay by cash, check, or bank transfer at our shop.
@elseif(! $invoice->isPaid())
Please contact us to arrange payment.
@endif

<x-mail::button :url="$viewInvoiceUrl">
View invoice online
</x-mail::button>

Questions? Reply to this email or call **{{ config('neamee.phone') }}**.

Thanks,<br>
{{ config('neamee.company_name') }}
</x-mail::message>
