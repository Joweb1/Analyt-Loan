<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guarantors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('custom_id')->nullable()->unique();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('bvn', 11)->nullable();
            $table->string('national_identity_number', 11)->nullable();
            $table->string('employer')->nullable();
            $table->decimal('income', 15, 2)->nullable();
            $table->json('custom_data')->nullable();
            $table->timestamps();
        });

        Schema::create('borrowers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('custom_id')->nullable()->unique();
            $table->foreignUuid('guarantor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignUuid('external_guarantor_id')->nullable()->constrained('guarantors')->nullOnDelete();
            $table->string('phone')->unique();
            $table->string('bvn', 11)->nullable();
            $table->string('national_identity_number', 11)->nullable();
            $table->integer('trust_score')->default(0);
            $table->string('credit_score')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('marital_status')->nullable();
            $table->integer('dependents')->nullable();
            $table->text('address')->nullable();
            $table->string('photo_url')->nullable();
            $table->string('passport_photograph')->nullable();
            $table->string('identity_document')->nullable();
            $table->string('bank_statement')->nullable();
            $table->string('income_proof')->nullable();
            $table->string('biometric_data')->nullable();
            $table->json('bank_account_details')->nullable();
            $table->json('employment_information')->nullable();
            $table->json('next_of_kin_details')->nullable();
            $table->json('custom_data')->nullable();
            $table->enum('kyc_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->integer('onboarding_step')->default(1);
            $table->boolean('portal_access')->default(false);
            $table->timestamps();
        });

        Schema::create('savings_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('borrower_id')->constrained()->cascadeOnDelete();
            $table->string('account_number')->unique();
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('interest_rate', 5, 2)->default(0);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('savings_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('savings_account_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('type'); // deposit, withdrawal, interest, etc.
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignUuid('staff_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('transaction_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_transactions');
        Schema::dropIfExists('savings_accounts');
        Schema::dropIfExists('borrowers');
        Schema::dropIfExists('guarantors');
    }
};
