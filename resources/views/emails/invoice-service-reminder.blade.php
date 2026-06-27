<x-mail::message>
@if ($reminderType === 'due')
# Your service is today

Hello {{ $invoice->user?->name ?? 'there' }},

This is a reminder that your next service at **{{ config('neamee.company_name') }}** is scheduled for **today**.
@else
# Upcoming service reminder

Hello {{ $invoice->user?->name ?? 'there' }},

This is a friendly reminder about your upcoming service at **{{ config('neamee.company_name') }}**.
@endif

**Invoice:** {{ $invoice->invoice_number }}  
@php
    $serviceLocal = app(\App\Services\InvoiceServiceReminderService::class)->serviceAtLocal($invoice);
    $tzLabel = \App\Services\InvoiceServiceReminderService::timezoneLabel($invoice->serviceReminderTimezone());
@endphp
**Service date:** {{ $serviceLocal?->format('l, M j, Y') }}  
**Service time:** {{ $serviceLocal?->format('g:i A') }} ({{ $tzLabel }})  
@if ($invoice->vehicle)
**Vehicle:** {{ $invoice->vehicle->year }} {{ $invoice->vehicle->make }} {{ $invoice->vehicle->model }}@if($invoice->vehicle->plate_number) ({{ $invoice->vehicle->plate_number }})@endif  
@endif
@if (filled($invoice->next_service_notes))
**Notes:** {{ $invoice->next_service_notes }}  
@endif

Please arrive a few minutes early. To reschedule, reply to this email or call **{{ config('neamee.phone') }}**.

<x-mail::button :url="\App\Support\FrontendUrl::portal('bookings')">
Book / view appointments
</x-mail::button>

Thanks,<br>
{{ config('neamee.company_name') }}
</x-mail::message>
