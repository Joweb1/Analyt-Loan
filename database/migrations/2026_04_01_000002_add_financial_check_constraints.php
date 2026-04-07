<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Add CHECK constraints to 'loans'
        DB::statement('ALTER TABLE loans ADD CONSTRAINT check_loan_amount_positive CHECK (amount >= 0)');
        DB::statement('ALTER TABLE loans ADD CONSTRAINT check_loan_interest_rate_positive CHECK (interest_rate >= 0)');
        DB::statement('ALTER TABLE loans ADD CONSTRAINT check_loan_penalty_value_positive CHECK (penalty_value >= 0)');

        // Add CHECK constraints to 'repayments'
        DB::statement('ALTER TABLE repayments ADD CONSTRAINT check_repayment_amount_positive CHECK (amount >= 0)');
        DB::statement('ALTER TABLE repayments ADD CONSTRAINT check_repayment_principal_positive CHECK (principal_amount >= 0)');
        DB::statement('ALTER TABLE repayments ADD CONSTRAINT check_repayment_interest_positive CHECK (interest_amount >= 0)');

        // Add CHECK constraints to 'scheduled_repayments'
        DB::statement('ALTER TABLE scheduled_repayments ADD CONSTRAINT check_scheduled_principal_positive CHECK (principal_amount >= 0)');
        DB::statement('ALTER TABLE scheduled_repayments ADD CONSTRAINT check_scheduled_interest_positive CHECK (interest_amount >= 0)');
        DB::statement('ALTER TABLE scheduled_repayments ADD CONSTRAINT check_scheduled_paid_amount_positive CHECK (paid_amount >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Dropping constraints for rollback support
        DB::statement('ALTER TABLE loans DROP CONSTRAINT check_loan_amount_positive');
        DB::statement('ALTER TABLE loans DROP CONSTRAINT check_loan_interest_rate_positive');
        DB::statement('ALTER TABLE loans DROP CONSTRAINT check_loan_penalty_value_positive');

        DB::statement('ALTER TABLE repayments DROP CONSTRAINT check_repayment_amount_positive');
        DB::statement('ALTER TABLE repayments DROP CONSTRAINT check_repayment_principal_positive');
        DB::statement('ALTER TABLE repayments DROP CONSTRAINT check_repayment_interest_positive');

        DB::statement('ALTER TABLE scheduled_repayments DROP CONSTRAINT check_scheduled_principal_positive');
        DB::statement('ALTER TABLE scheduled_repayments DROP CONSTRAINT check_scheduled_interest_positive');
        DB::statement('ALTER TABLE scheduled_repayments DROP CONSTRAINT check_scheduled_paid_amount_positive');
    }
};
