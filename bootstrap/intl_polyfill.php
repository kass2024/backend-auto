<?php

/**
 * Fallback when PHP intl extension is disabled (common on XAMPP / some cPanel hosts).
 * Filament uses NumberFormatter for format_money() and format_number().
 */
if (! extension_loaded('intl') && ! class_exists('NumberFormatter', false)) {
    class NumberFormatter
    {
        public const DECIMAL = 1;

        public const CURRENCY = 2;

        public function __construct(
            private string $locale,
            private int $style,
        ) {}

        public function formatCurrency(float $amount, string $currency): string
        {
            $symbol = match (strtoupper($currency)) {
                'USD' => '$',
                'EUR' => '€',
                'GBP' => '£',
                default => strtoupper($currency).' ',
            };

            $negative = $amount < 0;
            $formatted = $symbol.number_format(abs($amount), 2, '.', ',');

            return $negative ? '-'.$formatted : $formatted;
        }

        public function format(float|int $number, int $decimals = 0): string
        {
            return number_format((float) $number, $decimals, '.', ',');
        }
    }
}
