<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dateTime('next_service_at')->nullable()->after('odometer');
            $table->string('next_service_reminder_unit', 20)->nullable()->after('next_service_at');
            $table->text('next_service_notes')->nullable()->after('next_service_reminder_unit');
            $table->dateTime('next_service_reminder_early_sent_at')->nullable()->after('next_service_notes');
            $table->dateTime('next_service_reminder_due_sent_at')->nullable()->after('next_service_reminder_early_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'next_service_at',
                'next_service_reminder_unit',
                'next_service_notes',
                'next_service_reminder_early_sent_at',
                'next_service_reminder_due_sent_at',
            ]);
        });
    }
};
