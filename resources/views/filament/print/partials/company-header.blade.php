<table style="width:100%; border-collapse:collapse; margin-bottom:8px;">
    <tr>
        <td style="width:120px; vertical-align:top; border:none; padding:0 16px 0 0;">
            <img src="{{ asset(config('neamee.logo')) }}" alt="{{ config('neamee.company_name') }}" style="width:96px; height:96px; object-fit:contain; border-radius:50%;">
        </td>
        <td style="vertical-align:top; border:none; padding:0;">
            <div style="font-size:18px; font-weight:700; margin-bottom:6px;">{{ config('neamee.company_name') }}</div>
            <div style="font-size:13px; line-height:1.5;">
                {{ config('neamee.address_line1') }}<br>
                {{ config('neamee.address_line2') }}<br>
                {{ config('neamee.phone') }}
            </div>
        </td>
    </tr>
</table>
