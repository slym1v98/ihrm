<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruitment_requisitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('department_id');
            $table->string('position');
            $table->integer('headcount');
            $table->text('reason');
            $table->string('status', 20)->default('draft');
            $table->uuid('workflow_request_id')->nullable();
            $table->dateTime('opened_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->uuid('created_by');
            $table->timestamps();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruitment_requisitions');
    }
};
