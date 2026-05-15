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
        Schema::table('savings_withdrawals', function (Blueprint $table) {
            $table->uuid('organization_id')->change();
            $table->uuid('staff_id')->change();
            $table->uuid('approved_by')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('savings_withdrawals', function (Blueprint $table) {
            $table->foreignId('organization_id')->change();
            $table->foreignId('staff_id')->change();
            $table->foreignId('approved_by')->nullable()->change();
        });
    }
};
