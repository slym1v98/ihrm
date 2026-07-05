<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_enrollments', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('session_id');
            $t->uuid('employee_id');
            $t->dateTime('enrolled_at');
            $t->json('attendance')->nullable();
            $t->string('status', 20)->default('enrolled');
            $t->timestamps();
            $t->foreign('session_id')->references('id')->on('training_sessions')->cascadeOnDelete();
            $t->unique(['session_id', 'employee_id']);
            $t->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_enrollments');
    }
};
