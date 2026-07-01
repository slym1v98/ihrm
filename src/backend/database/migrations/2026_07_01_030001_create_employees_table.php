<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('employee_code', 50)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->date('dob')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('personal_email', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('address_street', 255)->nullable();
            $table->string('address_city', 100)->nullable();
            $table->string('address_province', 100)->nullable();
            $table->string('address_postal_code', 20)->nullable();
            $table->string('address_country', 100)->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->uuid('manager_id')->nullable();
            $table->foreignUuid('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignUuid('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignUuid('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('branch_id');
            $table->index('department_id');
            $table->index('position_id');
            $table->index('manager_id');
            $table->index('user_id');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreign('manager_id')->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
