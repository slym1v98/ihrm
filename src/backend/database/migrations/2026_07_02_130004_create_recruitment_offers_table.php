<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruitment_offers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('candidate_id')->unique();
            $table->uuid('requisition_id');
            $table->jsonb('terms');
            $table->string('status', 20)->default('draft');
            $table->dateTime('accepted_at')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->uuid('created_by');
            $table->foreign('candidate_id')->references('id')->on('recruitment_candidates')->cascadeOnDelete();
            $table->timestamps();
            $table->index('status');
        });
    }
    public function down(): void { Schema::dropIfExists('recruitment_offers'); }
};
