<?php

return [
    'company_name' => 'NEAMEE-AUTO-TECH SOLUTIONS',
    'address_line1' => '120 Bogle Lane',
    'address_line2' => 'Bowling Green, KY, 42101',
    'phone' => '(567) 329-9231',
    'logo' => 'images/logo/logo.png',

    /**
     * Public URL for links inside customer emails (PDF view, etc.).
     * Must match the server/database that sends invoice emails.
     */
    'email_app_url' => env('MAIL_APP_URL', env('APP_URL')),

    /**
     * Apply pending migrations on web requests (cPanel deploys).
     * Must live in config — env() is null after `php artisan config:cache`.
     */
    'auto_migrate' => filter_var(env('AUTO_MIGRATE', true), FILTER_VALIDATE_BOOLEAN),

    /**
     * Remittance details shown on invoices when a payment method is selected.
     */
    'payment_methods' => [
        'check' => [
            'payable_to' => env('PAY_CHECK_TO', 'NEAMEE-AUTO-TECH SOLUTIONS'),
        ],
        'bank_transfer' => [
            'bank' => env('PAY_BANK_NAME', 'Contact shop for bank details'),
            'account_name' => env('PAY_BANK_ACCOUNT_NAME', 'NEAMEE Auto-Tech / EGIDE'),
            'account_number' => env('PAY_BANK_ACCOUNT_NUMBER'),
            'routing' => env('PAY_BANK_ROUTING'),
        ],
        'mobile_money' => [
            'name' => env('PAY_MOBILE_MONEY_NAME', 'EGIDE'),
            'number' => env('PAY_MOBILE_MONEY_NUMBER', '(567) 329-9231'),
        ],
        'zelle' => [
            'name' => env('PAY_ZELLE_NAME', 'EGIDE'),
            'email_or_phone' => env('PAY_ZELLE_TARGET', '(567) 329-9231'),
        ],
        'cash_app' => [
            'name' => env('PAY_CASHAPP_NAME', 'Egide Niringiyimana'),
            'cashtag' => env('PAY_CASHAPP_TAG', '$EgideNiringiyimana'),
            'url' => env('PAY_CASHAPP_URL', 'https://cash.app/$EgideNiringiyimana'),
        ],
    ],

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
