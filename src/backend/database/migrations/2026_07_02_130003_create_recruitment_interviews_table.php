<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruitment_interviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('candidate_id');
            $table->uuid('requisition_id');
            $table->jsonb('interviewers');
            $table->dateTime('scheduled_at');
            $table->string('status', 20)->default('scheduled');
            $table->jsonb('scorecards')->nullable()->default('[]');
            $table->text('notes')->nullable();
            $table->foreign('candidate_id')->references('id')->on('recruitment_candidates')->cascadeOnDelete();
            $table->timestamps();
            $table->index('status');
        });
    }
    public function down(): void { Schema::dropIfExists('recruitment_interviews'); }
};
