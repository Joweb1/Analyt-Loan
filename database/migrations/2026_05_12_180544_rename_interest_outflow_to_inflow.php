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
            $table->renameColumn('loan_interest_outflow', 'loan_interest');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashbook_entries', function (Blueprint $table) {
            $table->renameColumn('loan_interest', 'loan_interest_outflow');
        });
    }
};
