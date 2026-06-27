<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('invoices', 'public_view_token')) {
            return;
        }

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('public_view_token', 64)->nullable()->unique()->after('customer_emailed_at');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('public_view_token');
        });
    }
};
