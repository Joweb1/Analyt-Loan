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
            $table->boolean('email_reminders_enabled')->default(true)->after('timezone');
            $table->boolean('loan_approval_alerts_enabled')->default(true)->after('email_reminders_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['email_reminders_enabled', 'loan_approval_alerts_enabled']);
        });
    }
};
