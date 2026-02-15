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
            $table->decimal('penalty_value', 10, 2)->default(0)->after('insurance_fee');
            $table->string('penalty_type')->default('fixed')->after('penalty_value'); // fixed, percentage
            $table->string('penalty_frequency')->default('one_time')->after('penalty_type'); // one_time, daily, weekly, monthly
            $table->boolean('override_system_penalty')->default(false)->after('penalty_frequency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn([
                'penalty_value',
                'penalty_type',
                'penalty_frequency',
                'override_system_penalty',
            ]);
        });
    }
};