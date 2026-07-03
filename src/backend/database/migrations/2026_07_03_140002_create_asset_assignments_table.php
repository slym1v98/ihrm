<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('asset_item_id');
            $table->uuid('employee_id');
            $table->timestamp('issued_at');
            $table->timestamp('expected_return_at')->nullable();
            $table->string('condition_on_issue');
            $table->string('status');
            $table->timestamps();
            $table->foreign('asset_item_id')->references('id')->on('asset_items')->cascadeOnDelete();
            $table->index(['employee_id', 'status']);
            $table->index(['asset_item_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_assignments');
    }
};
