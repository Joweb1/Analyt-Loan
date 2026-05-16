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
        Schema::create('cashbook_entries', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $blueprint->date('entry_date');
            $blueprint->text('description')->nullable();

            // Inflows
            $blueprint->bigInteger('loan_repayments')->default(0);
            $blueprint->bigInteger('savings_deposits')->default(0);
            $blueprint->bigInteger('registration_fees')->default(0);
            $blueprint->bigInteger('loan_processing_fees')->default(0);
            $blueprint->bigInteger('insurance_fees')->default(0);
            $blueprint->bigInteger('bank_withdrawals')->default(0);
            $blueprint->bigInteger('excess_cash')->default(0);
            $blueprint->bigInteger('card_payments')->default(0);
            $blueprint->bigInteger('daily_savings')->default(0);
            $blueprint->bigInteger('loan_interest')->default(0);

            // Outflows
            $blueprint->bigInteger('loan_disbursements')->default(0);
            $blueprint->bigInteger('savings_withdrawals')->default(0);
            $blueprint->bigInteger('default_amount')->default(0);
            $blueprint->bigInteger('charges')->default(0);
            $blueprint->bigInteger('bonuses')->default(0);

            // Expenses
            $blueprint->bigInteger('daily_expense_amount')->default(0);

            // Bank Channel (Expected Bank Transfers)
            $blueprint->bigInteger('expected_bank_transfers')->default(0);

            // End of Day & Reconciliation
            $blueprint->bigInteger('opening_cash')->default(0);
            $blueprint->bigInteger('expected_cash_at_hand')->default(0);
            $blueprint->bigInteger('actual_cash_at_hand')->default(0);
            $blueprint->bigInteger('bank_deposit_amount')->default(0);

            // Metadata & Verification
            $blueprint->string('status')->default('pending'); // pending, verified, discrepancy
            $blueprint->timestamp('verified_at')->nullable();
            $blueprint->string('audit_hash')->nullable();
            $blueprint->text('shortfall_report')->nullable();

            $blueprint->timestamps();

            // Indexing for performance
            $blueprint->index(['organization_id', 'entry_date']);
            $blueprint->unique(['organization_id', 'entry_date']);
        });

        Schema::create('expense_budgets', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $blueprint->integer('month'); // 1-12
            $blueprint->integer('year');
            $blueprint->bigInteger('total_budget_amount')->default(0);
            $blueprint->bigInteger('spent_amount')->default(0);
            $blueprint->text('notes')->nullable();
            $blueprint->timestamps();

            $blueprint->unique(['organization_id', 'month', 'year']);
        });

        Schema::create('account_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('organization_id')->constrained()->onDelete('cascade');
            $table->unsignedSmallInteger('month');
            $table->unsignedSmallInteger('year');
            $table->bigInteger('opening_balance')->default(0); // Starting bank balance for the month
            $table->timestamps();

            $table->unique(['organization_id', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_balances');
        Schema::dropIfExists('expense_budgets');
        Schema::dropIfExists('cashbook_entries');
    }
};
