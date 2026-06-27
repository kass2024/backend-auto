<x-mail::message>
# Your next appointment is scheduled

Hello {{ $booking->customer_name }},

We've scheduled your next service at **NEAMEE Auto-Tech Solutions** following your recent visit.

**Service:** {{ $booking->service?->name ?? 'General service' }}  
**Date:** {{ $booking->scheduled_date->format('l, M j, Y') }}  
**Time:** {{ substr((string) $booking->scheduled_time, 0, 5) }}  
@if($booking->vehicle)
**Vehicle:** {{ $booking->vehicle->year }} {{ $booking->vehicle->make }} {{ $booking->vehicle->model }} ({{ $booking->vehicle->plate_number }})  
@endif
**Reference:** {{ $booking->reference }}

@if($booking->staff_notes)
**Note from our team:**  
{{ $booking->staff_notes }}
@endif

<x-mail::button :url="\App\Support\FrontendUrl::portal()">
View in Customer Portal
</x-mail::button>

You'll also see a reminder in your dashboard. Need to reschedule? Reply to this email or call **+1 (567) 329-9231**.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
