<x-mail::message>
# Booking received

Hello {{ $booking->customer_name }},

Thank you for booking with **NEAMEE Auto-Tech Solutions**. We have received your request and will confirm shortly.

**Reference:** {{ $booking->reference }}  
**Service:** {{ $booking->service?->name ?? 'General service' }}  
**Date:** {{ $booking->scheduled_date->format('l, M j, Y') }}  
**Time:** {{ substr((string) $booking->scheduled_time, 0, 5) }}  
**Status:** Pending confirmation

<x-mail::button :url="rtrim(env('FRONTEND_URL', 'https://neamee-autotechsolutions.com'), '/').'/portal/bookings'">
View My Bookings
</x-mail::button>

Questions? Call **+1 (567) 329-9231** or reply to this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
