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
        Schema::table('organizations', function (Blueprint $table) {
            $table->renameColumn('operating_date', 'system_date');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->date('system_date')->nullable()->change();
        });

        // Initialize system_date for existing orgs
        DB::table('organizations')
            ->whereNull('system_date')
            ->update(['system_date' => now()->toDateString()]);

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('use_manual_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->boolean('use_manual_date')->default(false)->after('repayment_account_name');
            $table->renameColumn('system_date', 'operating_date');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->datetime('operating_date')->nullable()->change();
        });
    }
};
