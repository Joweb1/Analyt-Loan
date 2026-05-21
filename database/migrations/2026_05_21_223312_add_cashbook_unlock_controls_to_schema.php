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
            $table->integer('cashbook_unlock_limit')->default(3)->after('allow_flexible_repayments');
            $table->boolean('allow_staff_cashbook_unlock')->default(true)->after('cashbook_unlock_limit');
        });

        Schema::table('cashbook_entries', function (Blueprint $table) {
            $table->integer('staff_unlock_count')->default(0)->after('verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['cashbook_unlock_limit', 'allow_staff_cashbook_unlock']);
        });

        Schema::table('cashbook_entries', function (Blueprint $table) {
            $table->dropColumn('staff_unlock_count');
        });
    }
};
