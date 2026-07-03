<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offboarding_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id')->index();
            $table->string('type', 30);
            $table->text('reason');
            $table->date('requested_last_working_date');
            $table->date('approved_last_working_date')->nullable();
            $table->string('status', 20)->default('draft');
            $table->uuid('workflow_request_id')->nullable();
            $table->timestamps();
            $table->index(['employee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offboarding_requests');
    }
};
