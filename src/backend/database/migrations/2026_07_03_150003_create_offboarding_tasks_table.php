<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offboarding_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('offboarding_plan_id');
            $table->string('task_type', 20);
            $table->string('owner_type', 20);
            $table->string('owner_id', 100);
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status', 20)->default('pending');
            $table->boolean('requires_approval')->default(false);
            $table->uuid('approval_workflow_request_id')->nullable();
            $table->uuid('proof_file_object_id')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->foreign('offboarding_plan_id')->references('id')->on('offboarding_plans')->cascadeOnDelete();
            $table->index(['offboarding_plan_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offboarding_tasks');
    }
};
