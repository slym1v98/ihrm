<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('final_clearances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('offboarding_plan_id');
            $table->uuid('employee_id');
            $table->timestamp('cleared_at');
            $table->uuid('cleared_by');
            $table->boolean('asset_obligations_met')->default(false);
            $table->text('payroll_notes')->nullable();
            $table->timestamps();
            $table->foreign('offboarding_plan_id')->references('id')->on('offboarding_plans')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('final_clearances');
    }
};
