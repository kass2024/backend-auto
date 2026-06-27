<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('vehicles')
            ->orderBy('id')
            ->get(['id', 'plate_number'])
            ->each(function ($vehicle): void {
                DB::table('vehicles')
                    ->where('id', $vehicle->id)
                    ->update(['plate_number' => strtoupper(trim((string) $vehicle->plate_number))]);
            });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->unique('plate_number');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropUnique(['plate_number']);
        });
    }
};
