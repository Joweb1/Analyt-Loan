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
        Schema::table('repayments', function (Blueprint $table) {
            $table->string('borrower_id')->nullable()->after('loan_id')->index();
            $table->string('organization_id')->nullable()->after('borrower_id')->index();
            $table->text('notes')->nullable()->after('paid_at');
            $table->string('recorded_by')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repayments', function (Blueprint $table) {
            $table->dropColumn(['borrower_id', 'organization_id', 'notes', 'recorded_by']);
        });
    }
};
