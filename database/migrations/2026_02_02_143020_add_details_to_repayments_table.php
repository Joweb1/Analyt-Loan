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
            $table->string('payment_method')->nullable()->after('amount');
            $table->foreignUuid('collected_by')->nullable()->after('payment_method')->constrained('users')->nullOnDelete();
            $table->decimal('principal_amount', 10, 2)->default(0)->after('collected_by');
            $table->decimal('interest_amount', 10, 2)->default(0)->after('principal_amount');
            $table->decimal('extra_amount', 10, 2)->default(0)->after('interest_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('repayments', function (Blueprint $table) {
            $table->dropForeign(['collected_by']);
            $table->dropColumn(['payment_method', 'collected_by', 'principal_amount', 'interest_amount', 'extra_amount']);
        });
    }
};
