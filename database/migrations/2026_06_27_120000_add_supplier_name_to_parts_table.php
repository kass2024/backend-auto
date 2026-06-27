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
            $table->string('supplier_name')->nullable()->after('supplier_id');
        });

        if (Schema::hasTable('suppliers')) {
            $parts = DB::table('parts')->whereNotNull('supplier_id')->get(['id', 'supplier_id']);

            foreach ($parts as $part) {
                $name = DB::table('suppliers')->where('id', $part->supplier_id)->value('name');

                if ($name) {
                    DB::table('parts')->where('id', $part->id)->update(['supplier_name' => $name]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            $table->dropColumn('supplier_name');
        });
    }
};
