<x-mail::message>
# Quote request received

Hello {{ $quote->name }},

Thank you for contacting **NEAMEE Auto-Tech Solutions**. We received your quote request and will respond within 24 hours.

@if($quote->service)
**Service:** {{ $quote->service->name }}  
@endif
@if($quote->vehicle_make || $quote->vehicle_model)
**Vehicle:** {{ trim($quote->vehicle_make.' '.$quote->vehicle_model) }}  
@endif

We appreciate your interest and will be in touch soon.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
