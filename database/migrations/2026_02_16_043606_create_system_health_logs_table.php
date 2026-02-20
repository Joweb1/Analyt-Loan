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
        Schema::create('system_health_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('level')->default('info'); // info, success, warning, error
            $table->string('component'); // Database, Cache, Queue, Scheduler
            $table->text('message');
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_health_logs');
    }
};
