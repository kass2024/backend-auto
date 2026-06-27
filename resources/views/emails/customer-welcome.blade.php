<x-mail::message>
# Welcome to NEAMEE Auto-Tech Solutions

Hello {{ $customer->name }},

Your customer portal account is ready. Use the credentials below to sign in and view bookings, vehicles, and invoices.

**Portal:** [{{ $loginUrl }}]({{ $loginUrl }})

**Email:** {{ $customer->email }}  
**Temporary password:** `{{ $plainPassword }}`

<x-mail::button :url="$loginUrl">
Sign In to Customer Portal
</x-mail::button>

For security, please change your password after your first login.

Questions? Call us at **+1 (567) 329-9231** or reply to this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
