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
            $table->boolean('push_notifications_enabled')->default(true)->after('loan_approval_alerts_enabled');
            $table->boolean('repayment_notifications_enabled')->default(true)->after('push_notifications_enabled');
            $table->boolean('overdue_notifications_enabled')->default(true)->after('repayment_notifications_enabled');
            $table->boolean('new_borrower_notifications_enabled')->default(true)->after('overdue_notifications_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn([
                'push_notifications_enabled',
                'repayment_notifications_enabled',
                'overdue_notifications_enabled',
                'new_borrower_notifications_enabled',
            ]);
        });
    }
};
