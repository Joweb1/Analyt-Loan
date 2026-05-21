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
            $table->string('interest_calculation_type')->default('percentage')->after('default_interest_rate');
            $table->decimal('processing_fee', 15, 2)->nullable()->after('repayment_cycle');
            $table->string('processing_fee_type')->default('fixed')->after('processing_fee');
            $table->decimal('insurance_fee', 15, 2)->nullable()->after('processing_fee_type');
            $table->string('insurance_fee_type')->default('fixed')->after('insurance_fee');
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->string('interest_calculation_type')->default('percentage')->after('interest_rate');
            $table->string('insurance_fee_type')->default('fixed')->after('insurance_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_products', function (Blueprint $table) {
            $table->dropColumn([
                'interest_calculation_type',
                'processing_fee',
                'processing_fee_type',
                'insurance_fee',
                'insurance_fee_type',
            ]);
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn([
                'interest_calculation_type',
                'insurance_fee_type',
            ]);
        });
    }
};
