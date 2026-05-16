<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Organizations Updates
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('default_customer_password')->default('password')->after('allow_flexible_repayments');
            $table->renameColumn('operating_date', 'system_date');
            $table->integer('thrift_cycle_days')->default(6)->after('allow_flexible_repayments');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->date('system_date')->nullable()->change();
            $table->dropColumn('use_manual_date');
        });

        // Initialize system_date for existing orgs
        DB::table('organizations')
            ->whereNull('system_date')
            ->update(['system_date' => now()->toDateString()]);

        // 2. Savers Updates
        Schema::table('savers', function (Blueprint $table) {
            $table->string('custom_id')->nullable()->unique()->after('id');
            $table->foreignUuid('portfolio_id')->nullable()->after('organization_id')->constrained()->nullOnDelete();
            $table->boolean('is_daily_saver')->default(false)->after('portfolio_id');
            $table->bigInteger('daily_target_amount')->default(0)->after('is_daily_saver');
        });

        // 3. Guarantors Updates
        Schema::table('guarantors', function (Blueprint $table) {
            $table->foreignUuid('portfolio_id')->nullable()->after('organization_id')->constrained()->nullOnDelete();
        });

        // 4. Loans Updates
        Schema::table('loans', function (Blueprint $table) {
            $table->date('installment_date')->nullable()->after('release_date');
            $table->text('register_notes')->nullable()->after('description');
        });

        // 5. Borrowers Updates
        Schema::table('borrowers', function (Blueprint $table) {
            $table->string('collection_group')->nullable()->after('custom_id')->index();
            $table->boolean('is_daily_saver')->default(false)->after('collection_group');
            $table->bigInteger('daily_target_amount')->default(0)->after('is_daily_saver');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrowers', function (Blueprint $table) {
            $table->dropColumn(['collection_group', 'is_daily_saver', 'daily_target_amount']);
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['installment_date', 'register_notes']);
        });

        Schema::table('guarantors', function (Blueprint $table) {
            $table->dropForeign(['portfolio_id']);
            $table->dropColumn('portfolio_id');
        });

        Schema::table('savers', function (Blueprint $table) {
            $table->dropForeign(['portfolio_id']);
            $table->dropColumn(['custom_id', 'portfolio_id', 'is_daily_saver', 'daily_target_amount']);
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->boolean('use_manual_date')->default(false)->after('repayment_account_name');
            $table->renameColumn('system_date', 'operating_date');
            $table->dropColumn(['default_customer_password', 'thrift_cycle_days']);
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->datetime('operating_date')->nullable()->change();
        });
    }
};
