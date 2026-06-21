<x-mail::message>
# New booking submitted

A customer submitted a booking on the website.

**Reference:** {{ $booking->reference }}  
**Customer:** {{ $booking->customer_name }}  
**Email:** {{ $booking->customer_email }}  
**Phone:** {{ $booking->customer_phone }}  
**Service:** {{ $booking->service?->name ?? '—' }}  
**Date:** {{ $booking->scheduled_date->format('M j, Y') }} at {{ substr((string) $booking->scheduled_time, 0, 5) }}  
@if($booking->notes)
**Notes:** {{ $booking->notes }}
@endif

<x-mail::button :url="url('/admin/bookings/'.$booking->id.'/edit')">
Open in Admin
</x-mail::button>
</x-mail::message>
