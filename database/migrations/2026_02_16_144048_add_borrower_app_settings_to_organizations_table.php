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
            $table->string('tagline')->nullable()->after('name');
            $table->string('brand_color')->default('#0f172a')->after('tagline'); // Slate-900 default
            $table->string('repayment_bank_name')->nullable()->after('brand_color');
            $table->string('repayment_account_number')->nullable()->after('repayment_bank_name');
            $table->string('repayment_account_name')->nullable()->after('repayment_account_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn([
                'tagline',
                'brand_color',
                'repayment_bank_name',
                'repayment_account_number',
                'repayment_account_name',
            ]);
        });
    }
};
