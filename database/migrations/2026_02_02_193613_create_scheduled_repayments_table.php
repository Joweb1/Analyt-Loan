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
        Schema::create('scheduled_repayments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->date('due_date');
            $table->decimal('principal_amount', 10, 2);
            $table->decimal('interest_amount', 10, 2);
            $table->decimal('penalty_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->string('status')->default('pending'); // pending, paid, partial, overdue
            $table->integer('installment_number');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_repayments');
    }
};
