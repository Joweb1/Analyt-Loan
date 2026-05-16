<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Savings Transactions Updates
        Schema::table('savings_transactions', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->default('cash')->after('transaction_type');
        });

        // 2. Savings Withdrawals Table
        Schema::create('savings_withdrawals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->foreignUuid('savings_account_id')->constrained('savings_accounts')->cascadeOnDelete();
            $table->dateTime('transaction_date');
            $table->bigInteger('snapshot_balance')->default(0);
            $table->bigInteger('amount_withdrawn')->default(0);
            $table->bigInteger('loan_adjustment_amount')->default(0);
            $table->string('status')->default('pending'); // pending, processing, verified, approved, rejected
            $table->text('notes')->nullable();
            $table->foreignUuid('staff_id')->constrained('users');
            $table->foreignUuid('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->json('audit_trail')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index('transaction_date');
        });

        // 3. Repayments Updates
        Schema::table('repayments', function (Blueprint $table) {
            $table->uuid('borrower_id')->nullable()->after('loan_id')->index();
            $table->uuid('organization_id')->nullable()->after('borrower_id')->index();
            $table->bigInteger('fee_amount')->default(0)->after('interest_amount');
            $table->text('notes')->nullable()->after('paid_at');
            $table->string('recorded_by')->nullable()->after('notes');
        });

        // 4. Savings Accounts Updates
        Schema::table('savings_accounts', function (Blueprint $table) {
            $table->bigInteger('daily_savings_balance')->default(0)->after('balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('savings_accounts', function (Blueprint $table) {
            $table->dropColumn('daily_savings_balance');
        });

        Schema::table('repayments', function (Blueprint $table) {
            $table->dropColumn(['borrower_id', 'organization_id', 'fee_amount', 'notes', 'recorded_by']);
        });

        Schema::dropIfExists('savings_withdrawals');

        Schema::table('savings_transactions', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
};
