<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('next_service_timezone', 64)
                ->nullable()
                ->after('next_service_repeat');
        });

        \Illuminate\Support\Facades\DB::table('invoices')
            ->whereNotNull('next_service_at')
            ->whereNull('next_service_timezone')
            ->update(['next_service_timezone' => 'America/Chicago']);
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('next_service_timezone');
        });
    }
};
