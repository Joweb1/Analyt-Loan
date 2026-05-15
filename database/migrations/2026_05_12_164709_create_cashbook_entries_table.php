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
            $blueprint->foreignId('organization_id')->constrained()->cascadeOnDelete();
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

            // Outflows
            $blueprint->bigInteger('loan_disbursements')->default(0);
            $blueprint->bigInteger('savings_withdrawals')->default(0);
            $blueprint->bigInteger('other_outflows')->default(0);

            // Expenses
            $blueprint->bigInteger('daily_expense_amount')->default(0);

            // End of Day & Reconciliation
            $blueprint->bigInteger('opening_cash')->default(0);
            $blueprint->bigInteger('expected_cash_at_hand')->default(0);
            $blueprint->bigInteger('actual_cash_at_hand')->default(0);
            $blueprint->bigInteger('bank_deposit_amount')->default(0);

            // Metadata & Verification
            $blueprint->string('status')->default('pending'); // pending, verified, discrepancy
            $blueprint->timestamp('verified_at')->nullable();
            $blueprint->string('audit_hash')->nullable();

            $blueprint->timestamps();

            // Indexing for performance
            $blueprint->index(['organization_id', 'entry_date']);
            $blueprint->unique(['organization_id', 'entry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashbook_entries');
    }
};
