<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE invoices MODIFY COLUMN payment_method ENUM(
            'cash',
            'check',
            'bank_transfer',
            'credit_card',
            'mobile_money',
            'mpesa',
            'airtel_money',
            'zelle',
            'cash_app'
        ) NULL");
    }

    public function down(): void
    {
        DB::table('invoices')
            ->whereIn('payment_method', ['zelle', 'cash_app'])
            ->update(['payment_method' => 'cash']);

        DB::statement("ALTER TABLE invoices MODIFY COLUMN payment_method ENUM(
            'cash',
            'check',
            'bank_transfer',
            'credit_card',
            'mobile_money',
            'mpesa',
            'airtel_money'
        ) NULL");
    }
};
