<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_delegations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('delegator_id');
            $table->uuid('delegate_id');
            $table->string('role_type', 30)->nullable();
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->boolean('active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->index(['delegator_id', 'role_type', 'active']);
            $table->index(['delegate_id', 'active']);
            $table->index(['start_at', 'end_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_delegations');
    }
};
