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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('user_id')->nullable(); // Who the transaction is for (Customer)
            $table->uuid('performer_id')->nullable(); // Who performed the transaction (Staff/Admin)

            $table->string('type'); // deposit, withdrawal, loan_disbursement, repayment, registration_fee, penalty, interest, charge, bonus
            $table->bigInteger('amount'); // Minor units
            $table->string('currency_code', 3)->default('NGN');

            $table->string('reference')->unique();
            $table->string('payment_method')->nullable(); // cash, bank_transfer, card, etc.

            $table->uuid('related_id')->nullable(); // ID of the related model (Loan ID, Repayment ID, etc.)
            $table->string('related_type')->nullable(); // Loan, Repayment, SavingsTransaction

            $table->text('notes')->nullable();
            $table->date('transaction_date');

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->index(['organization_id', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
