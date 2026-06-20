<x-mail::message>
# Appointment reminder

Hello {{ $booking->customer_name }},

This is a friendly reminder about your upcoming appointment at **NEAMEE Auto-Tech Solutions**.

**Service:** {{ $booking->service?->name ?? 'General service' }}  
**Date:** {{ $booking->scheduled_date->format('l, M j, Y') }}  
**Time:** {{ substr((string) $booking->scheduled_time, 0, 5) }}  
@if($booking->vehicle)
**Vehicle:** {{ $booking->vehicle->year }} {{ $booking->vehicle->make }} {{ $booking->vehicle->model }}  
@endif
**Reference:** {{ $booking->reference }}

Please arrive a few minutes early. If you need to reschedule, contact us as soon as possible.

<x-mail::button :url="rtrim(env('FRONTEND_URL', 'https://neamee-autotechsolutions.com'), '/').'/portal/bookings'">
View My Bookings
</x-mail::button>

Call us at **+1 (567) 329-9231** with any questions.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
