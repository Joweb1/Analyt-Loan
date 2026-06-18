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
        Schema::table('savings_transactions', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->default('bank_transfer')->change();
        });

        Schema::table('repayments', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->default('bank_transfer')->change();
        });

        // Ensure transactions table also has a safe default if needed
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->default('bank_transfer')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('savings_transactions', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->default('cash')->change();
        });

        Schema::table('repayments', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->change();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->change();
        });
    }
};
