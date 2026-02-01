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
        Schema::table('borrowers', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('passport_photograph')->nullable();
            $table->string('biometric_data')->nullable();
            $table->string('national_identity_number')->nullable();
            $table->string('identity_document')->nullable();
            $table->text('bank_account_details')->nullable();
            $table->string('bank_statement')->nullable();
            $table->text('employment_information')->nullable();
            $table->string('income_proof')->nullable();
            $table->string('credit_score')->nullable();
            $table->string('marital_status')->nullable();
            $table->integer('dependents')->nullable();
            $table->text('next_of_kin_details')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrowers', function (Blueprint $table) {
            $table->dropColumn([
                'date_of_birth',
                'gender',
                'passport_photograph',
                'biometric_data',
                'national_identity_number',
                'identity_document',
                'bank_account_details',
                'bank_statement',
                'employment_information',
                'income_proof',
                'credit_score',
                'marital_status',
                'dependents',
                'next_of_kin_details',
            ]);
        });
    }
};
