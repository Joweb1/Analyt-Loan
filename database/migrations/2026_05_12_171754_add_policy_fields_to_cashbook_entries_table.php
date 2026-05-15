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
        Schema::table('cashbook_entries', function (Blueprint $table) {
            // New Inflow fields
            $table->bigInteger('card_payments')->default(0);

            // New Outflow fields
            $table->bigInteger('default_amount')->default(0);
            $table->bigInteger('charges')->default(0);
            $table->bigInteger('bonuses')->default(0);

            // Reporting
            $table->text('shortfall_report')->nullable();

            // Cleanup
            $table->dropColumn('other_outflows');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashbook_entries', function (Blueprint $table) {
            $table->dropColumn(['card_payments', 'default_amount', 'charges', 'bonuses', 'shortfall_report']);
            $table->bigInteger('other_outflows')->default(0);
        });
    }
};
