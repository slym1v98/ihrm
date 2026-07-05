<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruitment_candidates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('requisition_id')->nullable();
            $table->uuid('employee_id')->nullable();
            $table->string('full_name');
            $table->string('email', 255)->nullable()->unique();
            $table->string('phone', 50)->nullable()->unique();
            $table->string('source', 20);
            $table->string('cv_file_descriptor', 255)->nullable();
            $table->string('status', 20)->default('new');
            $table->text('notes')->nullable();
            $table->foreign('requisition_id')->references('id')->on('recruitment_requisitions')->nullOnDelete();
            $table->timestamps();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruitment_candidates');
    }
};
