<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('default_interest_rate', 5, 2)->nullable();
            $table->integer('default_duration')->nullable();
            $table->string('duration_unit');
            $table->string('repayment_cycle');
            $table->timestamps();
        });

        Schema::create('loans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('borrower_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('loan_officer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('guarantor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('external_guarantor_id')->nullable()->constrained('guarantors')->nullOnDelete();
            $table->string('loan_number')->unique();
            $table->string('loan_product');
            $table->decimal('amount', 15, 2);
            $table->decimal('interest_rate', 5, 2);
            $table->enum('interest_type', ['year', 'month', 'week', 'day']);
            $table->integer('duration');
            $table->enum('duration_unit', ['year', 'month', 'week', 'day']);
            $table->string('repayment_cycle');
            $table->integer('num_repayments');
            $table->decimal('processing_fee', 15, 2)->nullable();
            $table->string('processing_fee_type')->default('fixed');
            $table->decimal('insurance_fee', 15, 2)->nullable();
            $table->decimal('penalty_value', 15, 2)->default(0);
            $table->string('penalty_type')->default('fixed');
            $table->string('penalty_frequency')->default('one_time');
            $table->boolean('override_system_penalty')->default(false);
            $table->text('description')->nullable();
            $table->json('attachments')->nullable();
            $table->string('status')->default('active');
            $table->date('release_date')->nullable();
            $table->timestamps();
        });

        Schema::create('repayments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('loan_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->nullable();
            $table->foreignUuid('collected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('principal_amount', 15, 2)->default(0);
            $table->decimal('interest_amount', 15, 2)->default(0);
            $table->decimal('extra_amount', 15, 2)->default(0);
            $table->date('paid_at');
            $table->timestamps();
        });

        Schema::create('scheduled_repayments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('loan_id')->constrained()->cascadeOnDelete();
            $table->date('due_date');
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('interest_amount', 15, 2);
            $table->decimal('penalty_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->string('status')->default('pending');
            $table->integer('installment_number');
            $table->timestamps();
        });

        Schema::create('collaterals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('loan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('type');
            $table->string('condition')->nullable();
            $table->text('description')->nullable();
            $table->decimal('value', 15, 2);
            $table->string('image_path')->nullable();
            $table->json('documents')->nullable();
            $table->date('registered_date')->nullable();
            $table->string('status')->default('in_vault');
            $table->timestamps();
        });

        Schema::create('payment_proofs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('loan_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('borrower_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('payment_method');
            $table->string('reference_code')->unique();
            $table->string('receipt_path')->nullable();
            $table->string('status')->default('pending');
            $table->text('admin_notes')->nullable();
            $table->timestamp('paid_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_proofs');
        Schema::dropIfExists('collaterals');
        Schema::dropIfExists('scheduled_repayments');
        Schema::dropIfExists('repayments');
        Schema::dropIfExists('loans');
        Schema::dropIfExists('loan_products');
    }
};
