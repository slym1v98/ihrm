<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id')->index();
            $table->uuid('candidate_id')->nullable();
            $table->uuid('template_id')->nullable();
            $table->date('start_date');
            $table->string('status', 20)->default('draft');
            $table->uuid('workflow_request_id')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_plans');
    }
};
