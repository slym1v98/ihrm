<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('document_type', 50);
            $table->string('category', 100)->nullable();
            $table->string('file_path');
            $table->string('file_original_name', 255);
            $table->string('file_mime', 100);
            $table->bigInteger('file_size');
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();

            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};
