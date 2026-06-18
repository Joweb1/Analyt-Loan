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
            $table->string('collection_group')->nullable()->after('portfolio_id')->index();
        });

        Schema::table('scheduled_repayments', function (Blueprint $table) {
            $table->uuid('organization_id')->nullable()->after('loan_id')->index();
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
        });

        // Backfill organization_id for existing schedules
        DB::statement('UPDATE scheduled_repayments SET organization_id = (SELECT organization_id FROM loans WHERE loans.id = scheduled_repayments.loan_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn('collection_group');
        });

        Schema::table('scheduled_repayments', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });
    }
};
