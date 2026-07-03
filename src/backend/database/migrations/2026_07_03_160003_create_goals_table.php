<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('goals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('cycle_id');
            $table->uuid('employee_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('weight', 5, 2)->default(1.0);
            $table->text('target_value')->nullable();
            $table->text('actual_value')->nullable();
            $table->string('status', 20)->default('active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->foreign('cycle_id')->references('id')->on('performance_cycles')->cascadeOnDelete();
            $table->index(['cycle_id', 'employee_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('goals'); }
};
