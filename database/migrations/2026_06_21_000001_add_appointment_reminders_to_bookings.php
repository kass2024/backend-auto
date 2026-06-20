<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('job_card_id')->nullable()->after('mechanic_id')->constrained()->nullOnDelete();
            $table->foreignId('scheduled_by_user_id')->nullable()->after('job_card_id')->constrained('users')->nullOnDelete();
            $table->text('staff_notes')->nullable()->after('notes');
            $table->timestamp('confirmation_sent_at')->nullable()->after('status');
            $table->timestamp('reminder_sent_at')->nullable()->after('confirmation_sent_at');
            $table->timestamp('popup_dismissed_at')->nullable()->after('reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('job_card_id');
            $table->dropConstrainedForeignId('scheduled_by_user_id');
            $table->dropColumn(['staff_notes', 'confirmation_sent_at', 'reminder_sent_at', 'popup_dismissed_at']);
        });
    }
};
