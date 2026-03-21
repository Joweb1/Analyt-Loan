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
        Schema::create('portfolios', function (Blueprint $blueprint) {
            $blueprint->uuid('id')->primary();
            $blueprint->foreignUuid('organization_id')->constrained()->cascadeOnDelete();
            $blueprint->string('name');
            $blueprint->text('description')->nullable();
            $blueprint->timestamps();
        });

        Schema::create('portfolio_user', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignUuid('portfolio_id')->constrained()->cascadeOnDelete();
            $blueprint->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portfolio_user');
        Schema::dropIfExists('portfolios');
    }
};
