<x-mail::message>
# New quote request

**Name:** {{ $quote->name }}  
**Email:** {{ $quote->email }}  
**Phone:** {{ $quote->phone }}  
**Service:** {{ $quote->service?->name ?? 'General inquiry' }}  
@if($quote->vehicle_make || $quote->vehicle_model)
**Vehicle:** {{ trim($quote->vehicle_make.' '.$quote->vehicle_model) }}  
@endif
@if($quote->message)
**Message:**  
{{ $quote->message }}
@endif

<x-mail::button :url="url('/admin/quote-requests/'.$quote->id.'/edit')">
Open in Admin
</x-mail::button>
</x-mail::message>
