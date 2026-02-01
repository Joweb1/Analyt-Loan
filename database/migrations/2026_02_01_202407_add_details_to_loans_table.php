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
        Schema::table('loans', function (Blueprint $table) {
            $table->string('loan_number')->unique()->after('id');
            $table->string('loan_product')->nullable()->after('amount');
            $table->date('release_date')->nullable()->after('loan_product');
            $table->decimal('interest_rate', 5, 2)->default(0)->after('release_date');
            $table->string('interest_type')->default('year')->after('interest_rate'); // year, month, week, day
            $table->integer('duration')->default(1)->after('interest_type');
            $table->string('duration_unit')->default('month')->after('duration'); // year, month, week, day
            $table->string('repayment_cycle')->default('monthly')->after('duration_unit'); // daily, weekly, biweekly, monthly, yearly
            $table->integer('num_repayments')->default(1)->after('repayment_cycle');
            $table->decimal('processing_fee', 10, 2)->nullable()->after('num_repayments');
            $table->string('processing_fee_type')->nullable()->after('processing_fee'); // fixed, percentage
            $table->decimal('insurance_fee', 10, 2)->nullable()->after('processing_fee_type');
            $table->text('description')->nullable()->after('insurance_fee');
            $table->json('attachments')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn([
                'loan_number',
                'loan_product',
                'release_date',
                'interest_rate',
                'interest_type',
                'duration',
                'duration_unit',
                'repayment_cycle',
                'num_repayments',
                'processing_fee',
                'processing_fee_type',
                'insurance_fee',
                'description',
                'attachments',
            ]);
        });
    }
};
