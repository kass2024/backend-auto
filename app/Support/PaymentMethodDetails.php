<?php

namespace App\Support;

class PaymentMethodDetails
{
    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            'cash' => 'Cash',
            'check' => 'Check',
            'bank_transfer' => 'Bank Transfer',
            'credit_card' => 'Credit Card (Stripe)',
            'mobile_money' => 'Mobile Money',
            'zelle' => 'Zelle',
            'cash_app' => 'Cash App',
        ];
    }

    public static function label(?string $method): ?string
    {
        if (blank($method)) {
            return null;
        }

        return self::options()[$method] ?? null;
    }

    /**
     * Remittance instructions shown when a method is selected.
     *
     * @return array{title: string, lines: array<int, string>, link: ?string, show_qr: bool}
     */
    public static function details(?string $method): array
    {
        $config = config('neamee.payment_methods', []);
        $method = (string) $method;

        return match ($method) {
            'cash' => [
                'title' => 'Cash',
                'lines' => [
                    'Pay in person at our shop.',
                    config('neamee.address_line1').', '.config('neamee.address_line2'),
                    'Phone: '.config('neamee.phone'),
                ],
                'link' => null,
                'show_qr' => false,
            ],
            'check' => [
                'title' => 'Check',
                'lines' => [
                    'Make checks payable to: '.($config['check']['payable_to'] ?? config('neamee.company_name')),
                    'Bring or mail to: '.config('neamee.address_line1').', '.config('neamee.address_line2'),
                ],
                'link' => null,
                'show_qr' => false,
            ],
            'bank_transfer' => [
                'title' => 'Bank Transfer',
                'lines' => array_values(array_filter([
                    'Bank: '.($config['bank_transfer']['bank'] ?? 'Contact shop for bank details'),
                    filled($config['bank_transfer']['account_name'] ?? null) ? 'Account name: '.$config['bank_transfer']['account_name'] : null,
                    filled($config['bank_transfer']['account_number'] ?? null) ? 'Account #: '.$config['bank_transfer']['account_number'] : null,
                    filled($config['bank_transfer']['routing'] ?? null) ? 'Routing #: '.$config['bank_transfer']['routing'] : null,
                ])),
                'link' => null,
                'show_qr' => false,
            ],
            'credit_card' => [
                'title' => 'Credit Card (Stripe)',
                'lines' => [
                    'Pay securely online with the Stripe link included in your invoice email.',
                    'Cards are also accepted at the shop.',
                ],
                'link' => null,
                'show_qr' => false,
            ],
            'mobile_money' => [
                'title' => 'Mobile Money',
                'lines' => array_values(array_filter([
                    'Send to: '.($config['mobile_money']['name'] ?? 'EGIDE'),
                    filled($config['mobile_money']['number'] ?? null) ? 'Number: '.$config['mobile_money']['number'] : 'Phone: '.config('neamee.phone'),
                    'Include your invoice number in the payment note.',
                ])),
                'link' => null,
                'show_qr' => false,
            ],
            'zelle' => [
                'title' => 'Zelle',
                'lines' => array_values(array_filter([
                    'Send via Zelle to: '.($config['zelle']['name'] ?? 'EGIDE'),
                    filled($config['zelle']['email_or_phone'] ?? null)
                        ? 'Zelle email/phone: '.$config['zelle']['email_or_phone']
                        : 'Use phone: '.config('neamee.phone'),
                    'Include your invoice number in the memo.',
                    'Scan the Zelle QR code below in your banking app.',
                ])),
                'link' => null,
                'show_qr' => true,
                'qr_key' => 'zelle',
            ],
            'cash_app' => [
                'title' => 'Cash App',
                'lines' => array_values(array_filter([
                    'Pay: '.($config['cash_app']['name'] ?? 'Egide Niringiyimana'),
                    'Cashtag: '.($config['cash_app']['cashtag'] ?? '$EgideNiringiyimana'),
                    'Scan the Cash App QR below, or open the link on your phone.',
                ])),
                'link' => $config['cash_app']['url'] ?? 'https://cash.app/$EgideNiringiyimana',
                'show_qr' => true,
                'qr_key' => 'cash_app',
            ],
            default => [
                'title' => self::label($method) ?? 'Payment',
                'lines' => ['Contact us for payment instructions: '.config('neamee.phone')],
                'link' => null,
                'show_qr' => false,
                'qr_key' => null,
            ],
        };
    }

    public static function showsQr(?string $method): bool
    {
        return (bool) (self::details($method)['show_qr'] ?? false);
    }

    public static function qrKey(?string $method): ?string
    {
        $key = self::details($method)['qr_key'] ?? null;

        return filled($key) ? (string) $key : null;
    }

    public static function adminHelperText(?string $method): string
    {
        $details = self::details($method);
        $lines = implode(' · ', $details['lines']);

        if ($details['link']) {
            $lines .= ' · '.$details['link'];
        }

        if (($details['qr_key'] ?? null) === 'zelle') {
            $lines .= ' · Zelle QR will appear on the printed/emailed invoice.';
        }

        if (($details['qr_key'] ?? null) === 'cash_app') {
            $lines .= ' · Cash App QR (US) will appear on the printed/emailed invoice.';
        }

        return $lines;
    }
}
