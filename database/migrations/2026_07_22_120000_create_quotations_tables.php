<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quote_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();

            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();

            $table->string('vehicle_make')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->unsignedSmallInteger('vehicle_year')->nullable();
            $table->string('vehicle_vin')->nullable();
            $table->string('vehicle_plate')->nullable();

            $table->text('problem_description')->nullable();
            $table->text('inspection_findings')->nullable();
            $table->text('proposed_repairs')->nullable();

            $table->decimal('parts_total', 10, 2)->default(0);
            $table->decimal('labor_total', 10, 2)->default(0);
            $table->decimal('additional_total', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            $table->enum('status', [
                'draft', 'sent', 'viewed', 'accepted', 'declined', 'expired', 'converted',
            ])->default('draft');

            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('public_view_token', 64)->nullable()->unique();
            $table->timestamp('customer_emailed_at')->nullable();

            $table->string('signature_name')->nullable();
            $table->longText('signature_data')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->string('signer_ip', 45)->nullable();

            $table->text('payment_terms')->nullable();
            $table->text('warranty_terms')->nullable();
            $table->text('authorization_terms')->nullable();

            $table->timestamps();
        });

        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['part', 'labor', 'additional'])->default('part');
            $table->foreignId('part_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        if (Schema::hasTable('invoices') && ! Schema::hasColumn('invoices', 'quotation_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignId('quotation_id')->nullable()->after('job_card_id')->constrained()->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'quotation_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropConstrainedForeignId('quotation_id');
            });
        }

        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
    }
};
