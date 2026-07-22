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
            'airtel_money'
        ) NULL");
    }

    public function down(): void
    {
        DB::table('invoices')
            ->where('payment_method', 'check')
            ->update(['payment_method' => 'cash']);

        DB::statement("ALTER TABLE invoices MODIFY COLUMN payment_method ENUM(
            'cash',
            'bank_transfer',
            'credit_card',
            'mobile_money',
            'mpesa',
            'airtel_money'
        ) NULL");
    }
};
