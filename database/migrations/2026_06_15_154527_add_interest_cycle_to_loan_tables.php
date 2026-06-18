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
        Schema::table('loan_products', function (Blueprint $table) {
            $table->string('interest_cycle')->default('month')->after('interest_calculation_type');
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->string('interest_cycle')->default('month')->after('interest_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_products', function (Blueprint $table) {
            $table->dropColumn('interest_cycle');
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('interest_cycle');
        });
    }
};
