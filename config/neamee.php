<?php

return [
    'company_name' => 'NEAMEE-AUTO-TECH SOLUTIONS',
    'address_line1' => '120 Bogle Lane',
    'address_line2' => 'Bowling Green, KY, 42101',
    'phone' => '(567) 329-9231',
    'logo' => 'images/logo/logo.png',

    /**
     * Public URL for links inside customer emails (PDF view, etc.).
     * Use your live API/site URL — never localhost (Gmail blocks those emails).
     */
    'email_app_url' => env('MAIL_APP_URL', env('APP_URL')),

    /** Invoice & customer-facing print/PDF branding (matches Filament admin + logo) */
    'brand' => [
        'primary' => '#556332',
        'primary_dark' => '#434f29',
        'primary_light' => '#6d8a3c',
        'accent' => '#8fad52',
        'surface' => '#f4f6ef',
        'surface_alt' => '#e4e9d8',
        'border' => '#c9d4b3',
        'border_strong' => '#a8b886',
        'page_bg' => '#eef1e8',
        'text' => '#1a1f14',
        'text_muted' => '#515c4a',
        'paid' => '#166534',
        'paid_bg' => '#dcfce7',
        'unpaid' => '#b45309',
        'unpaid_bg' => '#fef3c7',
    ],
];
