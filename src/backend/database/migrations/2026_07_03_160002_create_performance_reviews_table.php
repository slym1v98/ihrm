<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('cycle_id');
            $table->uuid('employee_id');
            $table->json('self_assessment')->nullable();
            $table->json('manager_assessment')->nullable();
            $table->json('hr_assessment')->nullable();
            $table->decimal('final_score', 8, 2)->nullable();
            $table->string('status', 20)->default('pending_self');
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
            $table->foreign('cycle_id')->references('id')->on('performance_cycles')->cascadeOnDelete();
            $table->unique(['cycle_id', 'employee_id']);
            $table->index(['cycle_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
    }
};
