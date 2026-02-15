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
        Schema::table('organizations', function (Blueprint $table) {
            $table->decimal('default_interest_rate', 5, 2)->default(4.5)->after('kyc_document_path');
            $table->integer('grace_period_days')->default(3)->after('default_interest_rate');
            $table->string('currency_code')->default('NGN')->after('grace_period_days');
            $table->string('timezone')->default('Africa/Lagos')->after('currency_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['default_interest_rate', 'grace_period_days', 'currency_code', 'timezone']);
        });
    }
};
