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
        Schema::create('savings_withdrawals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->foreignUuid('savings_account_id')->constrained('savings_accounts')->cascadeOnDelete();
            $table->dateTime('transaction_date');
            $table->bigInteger('snapshot_balance')->default(0);
            $table->bigInteger('amount_withdrawn')->default(0);
            $table->bigInteger('loan_adjustment_amount')->default(0);
            $table->string('status')->default('pending'); // pending, processing, verified, approved, rejected
            $table->text('notes')->nullable();
            $table->foreignUuid('staff_id')->constrained('users');
            $table->foreignUuid('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->json('audit_trail')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'status']);
            $table->index('transaction_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('savings_withdrawals');
    }
};
