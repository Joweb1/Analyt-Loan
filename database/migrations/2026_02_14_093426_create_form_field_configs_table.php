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
        Schema::create('form_field_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('section'); // Identity, Documents, Family, Financial, Guarantor, Password
            $table->string('name'); // field name/key
            $table->string('label');
            $table->string('type')->default('text'); // text, number, date, select, file, textarea
            $table->json('options')->nullable(); // For select inputs
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false); // If true, cannot be deleted (but maybe disabled if optional)
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['organization_id', 'section', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_field_configs');
    }
};
