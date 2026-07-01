<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('contract_number', 50)->unique();
            $table->string('contract_type', 50);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('sign_date')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->uuid('predecessor_contract_id')->nullable();
            $table->decimal('base_salary', 15, 2)->nullable();
            $table->foreignUuid('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->timestamps();

            $table->index('employee_id');
        });

        Schema::table('employee_contracts', function (Blueprint $table) {
            $table->foreign('predecessor_contract_id')->references('id')->on('employee_contracts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_contracts');
    }
};
