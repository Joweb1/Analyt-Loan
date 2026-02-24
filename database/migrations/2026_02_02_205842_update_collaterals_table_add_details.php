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
        Schema::table('collaterals', function (Blueprint $table) {
            if (! Schema::hasColumn('collaterals', 'type')) {
                $table->string('type')->default('Other')->after('name'); // Vehicle, Real Estate, Jewelry, etc.
            }
            if (! Schema::hasColumn('collaterals', 'condition')) {
                $table->string('condition')->nullable()->after('type'); // New, Used, Good, Poor
            }
            if (! Schema::hasColumn('collaterals', 'documents')) {
                $table->json('documents')->nullable()->after('image_path');
            }
            if (! Schema::hasColumn('collaterals', 'registered_date')) {
                $table->date('registered_date')->nullable()->after('documents');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('collaterals', function (Blueprint $table) {
            $table->dropColumn(['type', 'condition', 'documents', 'registered_date']);
        });
    }
};
