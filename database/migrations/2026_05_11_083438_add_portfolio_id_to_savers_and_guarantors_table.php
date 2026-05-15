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
        Schema::table('savers', function (Blueprint $table) {
            $table->foreignUuid('portfolio_id')->nullable()->after('organization_id')->constrained()->nullOnDelete();
        });

        Schema::table('guarantors', function (Blueprint $table) {
            $table->foreignUuid('portfolio_id')->nullable()->after('organization_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('savers', function (Blueprint $table) {
            $table->dropForeign(['portfolio_id']);
            $table->dropColumn('portfolio_id');
        });

        Schema::table('guarantors', function (Blueprint $table) {
            $table->dropForeign(['portfolio_id']);
            $table->dropColumn('portfolio_id');
        });
    }
};
