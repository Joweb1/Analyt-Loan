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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignUuid('organization_id')->nullable()->after('id'); // Nullable for App Owner or initial seeding
        });

        Schema::table('borrowers', function (Blueprint $table) {
            $table->foreignUuid('organization_id')->nullable()->after('id');
            $table->json('custom_data')->nullable()->after('next_of_kin_details');
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->foreignUuid('organization_id')->nullable()->after('id');
            $table->foreignUuid('loan_officer_id')->nullable()->after('borrower_id'); // For staff assignment
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('organization_id');
        });

        Schema::table('borrowers', function (Blueprint $table) {
            $table->dropColumn(['organization_id', 'custom_data']);
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['organization_id', 'loan_officer_id']);
        });
    }
};
