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
            $table->integer('thrift_cycle_days')->default(6)->after('allow_flexible_repayments');
        });

        Schema::table('borrowers', function (Blueprint $table) {
            $table->boolean('is_daily_saver')->default(false)->after('collection_group');
            $table->bigInteger('daily_target_amount')->default(0)->after('is_daily_saver');
        });

        Schema::table('savers', function (Blueprint $table) {
            $table->boolean('is_daily_saver')->default(false)->after('portfolio_id');
            $table->bigInteger('daily_target_amount')->default(0)->after('is_daily_saver');
        });

        Schema::table('savings_accounts', function (Blueprint $table) {
            $table->bigInteger('daily_savings_balance')->default(0)->after('balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('thrift_cycle_days');
        });

        Schema::table('borrowers', function (Blueprint $table) {
            $table->dropColumn(['is_daily_saver', 'daily_target_amount']);
        });

        Schema::table('savers', function (Blueprint $table) {
            $table->dropColumn(['is_daily_saver', 'daily_target_amount']);
        });

        Schema::table('savings_accounts', function (Blueprint $table) {
            $table->dropColumn('daily_savings_balance');
        });
    }
};
