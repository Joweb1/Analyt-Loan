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
        Schema::create('expense_budgets', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $blueprint->integer('month'); // 1-12
            $blueprint->integer('year');
            $blueprint->bigInteger('total_budget_amount')->default(0);
            $blueprint->bigInteger('spent_amount')->default(0);
            $blueprint->text('notes')->nullable();
            $blueprint->timestamps();

            $blueprint->unique(['organization_id', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_budgets');
    }
};
