<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Scale existing data by 100 to convert from major units (decimal) to minor units (integer)
        $this->scaleData(100);

        // Organization
        Schema::table('organizations', function (Blueprint $table) {
            $table->bigInteger('default_interest_rate')->default(0)->change();
        });

        // Savings Accounts
        Schema::table('savings_accounts', function (Blueprint $table) {
            $table->bigInteger('balance')->default(0)->change();
            $table->bigInteger('interest_rate')->default(0)->change();
        });

        // Savings Transactions
        Schema::table('savings_transactions', function (Blueprint $table) {
            $table->bigInteger('amount')->change();
        });

        // Loans
        Schema::table('loans', function (Blueprint $table) {
            $table->bigInteger('amount')->change();
            $table->bigInteger('processing_fee')->nullable()->change();
            $table->bigInteger('insurance_fee')->nullable()->change();
            $table->bigInteger('penalty_value')->default(0)->change();
        });

        // Repayments
        Schema::table('repayments', function (Blueprint $table) {
            $table->bigInteger('amount')->change();
            $table->bigInteger('principal_amount')->default(0)->change();
            $table->bigInteger('interest_amount')->default(0)->change();
            $table->bigInteger('extra_amount')->default(0)->change();
        });

        // Scheduled Repayments
        Schema::table('scheduled_repayments', function (Blueprint $table) {
            $table->bigInteger('principal_amount')->change();
            $table->bigInteger('interest_amount')->change();
            $table->bigInteger('penalty_amount')->default(0)->change();
            $table->bigInteger('paid_amount')->default(0)->change();
        });

        // Collaterals
        Schema::table('collaterals', function (Blueprint $table) {
            $table->bigInteger('value')->change();
        });

        // Payment Proofs
        Schema::table('payment_proofs', function (Blueprint $table) {
            $table->bigInteger('amount')->change();
        });

        // Borrowers (Read Model)
        Schema::table('borrowers', function (Blueprint $table) {
            $table->bigInteger('total_debt')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Organization
        Schema::table('organizations', function (Blueprint $table) {
            $table->decimal('default_interest_rate', 5, 2)->default(0)->change();
        });

        Schema::table('savings_accounts', function (Blueprint $table) {
            $table->decimal('balance', 15, 2)->default(0)->change();
            $table->decimal('interest_rate', 5, 2)->default(0)->change();
        });

        Schema::table('savings_transactions', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->change();
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->change();
            $table->decimal('processing_fee', 15, 2)->nullable()->change();
            $table->decimal('insurance_fee', 15, 2)->nullable()->change();
            $table->decimal('penalty_value', 15, 2)->default(0)->change();
        });

        Schema::table('repayments', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->change();
            $table->decimal('principal_amount', 15, 2)->default(0)->change();
            $table->decimal('interest_amount', 15, 2)->default(0)->change();
            $table->decimal('extra_amount', 15, 2)->default(0)->change();
        });

        Schema::table('scheduled_repayments', function (Blueprint $table) {
            $table->decimal('principal_amount', 15, 2)->change();
            $table->decimal('interest_amount', 15, 2)->change();
            $table->decimal('penalty_amount', 15, 2)->default(0)->change();
            $table->decimal('paid_amount', 15, 2)->default(0)->change();
        });

        Schema::table('collaterals', function (Blueprint $table) {
            $table->decimal('value', 15, 2)->change();
        });

        Schema::table('payment_proofs', function (Blueprint $table) {
            $table->decimal('amount', 15, 2)->change();
        });

        Schema::table('borrowers', function (Blueprint $table) {
            $table->decimal('total_debt', 15, 2)->default(0)->change();
        });

        // Scale data back down by 100
        $this->scaleData(0.01);
    }

    private function scaleData(float $factor): void
    {
        DB::table('organizations')->update(['default_interest_rate' => DB::raw("default_interest_rate * $factor")]);
        DB::table('savings_accounts')->update([
            'balance' => DB::raw("balance * $factor"),
            'interest_rate' => DB::raw("interest_rate * $factor"),
        ]);
        DB::table('savings_transactions')->update(['amount' => DB::raw("amount * $factor")]);
        DB::table('loans')->update([
            'amount' => DB::raw("amount * $factor"),
            'processing_fee' => DB::raw("processing_fee * $factor"),
            'insurance_fee' => DB::raw("insurance_fee * $factor"),
            'penalty_value' => DB::raw("penalty_value * $factor"),
        ]);
        DB::table('repayments')->update([
            'amount' => DB::raw("amount * $factor"),
            'principal_amount' => DB::raw("principal_amount * $factor"),
            'interest_amount' => DB::raw("interest_amount * $factor"),
            'extra_amount' => DB::raw("extra_amount * $factor"),
        ]);
        DB::table('scheduled_repayments')->update([
            'principal_amount' => DB::raw("principal_amount * $factor"),
            'interest_amount' => DB::raw("interest_amount * $factor"),
            'penalty_amount' => DB::raw("penalty_amount * $factor"),
            'paid_amount' => DB::raw("paid_amount * $factor"),
        ]);
        DB::table('collaterals')->update(['value' => DB::raw("value * $factor")]);
        DB::table('payment_proofs')->update(['amount' => DB::raw("amount * $factor")]);
        DB::table('borrowers')->update(['total_debt' => DB::raw("total_debt * $factor")]);
    }
};
