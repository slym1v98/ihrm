<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offboarding_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('offboarding_request_id')->index();
            $table->string('status', 20)->default('draft');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offboarding_plans');
    }
};
