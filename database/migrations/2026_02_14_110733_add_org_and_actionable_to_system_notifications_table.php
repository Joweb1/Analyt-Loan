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
        Schema::table('system_notifications', function (Blueprint $table) {
            $table->foreignUuid('organization_id')->nullable()->after('id');
            $table->boolean('is_actionable')->default(false)->after('category');
            $table->string('action_link')->nullable()->after('is_actionable');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('low')->after('action_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('system_notifications', function (Blueprint $table) {
            $table->dropColumn(['organization_id', 'is_actionable', 'action_link', 'priority']);
        });
    }
};
