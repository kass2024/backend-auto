<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->nullable()->after('job_card_id')->constrained()->nullOnDelete();
            $table->text('work_description')->nullable()->after('due_date');
            $table->decimal('labor_total', 10, 2)->default(0)->after('subtotal');
            $table->decimal('parts_total', 10, 2)->default(0)->after('labor_total');
            $table->string('stripe_checkout_session_id')->nullable()->after('payment_method');
            $table->text('stripe_payment_url')->nullable()->after('stripe_checkout_session_id');
            $table->dateTime('time_received')->nullable()->after('stripe_payment_url');
            $table->dateTime('time_promised')->nullable()->after('time_received');
            $table->unsignedInteger('odometer')->nullable()->after('time_promised');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->enum('type', ['part', 'service', 'other'])->default('other')->after('invoice_id');
            $table->foreignId('part_id')->nullable()->after('type')->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->after('part_id')->constrained()->nullOnDelete();
            $table->string('part_number')->nullable()->after('service_id');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('total');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('part_id');
            $table->dropConstrainedForeignId('service_id');
            $table->dropColumn(['type', 'part_number', 'sort_order']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('vehicle_id');
            $table->dropColumn([
                'work_description',
                'labor_total',
                'parts_total',
                'stripe_checkout_session_id',
                'stripe_payment_url',
                'time_received',
                'time_promised',
                'odometer',
            ]);
        });
    }
};
