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
        Schema::table('cashbook_entries', function (Blueprint $table) {
            // New Inflow
            $table->bigInteger('daily_savings')->default(0);

            // New Outflow
            $table->bigInteger('loan_interest_outflow')->default(0);

            // Bank Channel (Expected Bank Transfers)
            $table->bigInteger('expected_bank_transfers')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashbook_entries', function (Blueprint $table) {
            $table->dropColumn(['daily_savings', 'loan_interest_outflow', 'expected_bank_transfers']);
        });
    }
};
