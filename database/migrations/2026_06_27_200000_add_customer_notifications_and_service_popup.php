<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dateTime('next_service_popup_dismissed_at')->nullable()->after('next_service_reminder_due_sent_at');
        });

        Schema::create('customer_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 40)->default('service_reminder');
            $table->string('kind', 20);
            $table->string('title');
            $table->text('message');
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'dismissed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_notifications');

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('next_service_popup_dismissed_at');
        });
    }
};
