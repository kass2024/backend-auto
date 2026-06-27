<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            $table->string('part_no')->nullable()->after('sku');
            $table->string('category')->nullable()->after('name');
            $table->string('brand')->nullable()->after('category');
            $table->string('vehicle_model')->nullable()->after('brand');
            $table->unsignedSmallInteger('vehicle_year')->nullable()->after('vehicle_model');
            $table->string('manufacturer_part_number')->nullable()->after('vehicle_year');
        });

        foreach (DB::table('parts')->orderBy('id')->get() as $part) {
            DB::table('parts')->where('id', $part->id)->update([
                'part_no' => $part->part_no ?? $part->sku,
                'category' => $part->category ?? 'General',
                'brand' => $part->brand ?? $part->name,
                'manufacturer_part_number' => $part->manufacturer_part_number ?? $part->barcode,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            $table->dropColumn([
                'part_no',
                'category',
                'brand',
                'vehicle_model',
                'vehicle_year',
                'manufacturer_part_number',
            ]);
        });
    }
};
