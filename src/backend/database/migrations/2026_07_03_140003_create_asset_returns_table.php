<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_returns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('asset_assignment_id')->unique();
            $table->timestamp('returned_at');
            $table->string('condition_on_return');
            $table->text('notes')->nullable();
            $table->decimal('settlement_amount', 12, 2)->default(0);
            $table->timestamps();
            $table->foreign('asset_assignment_id')->references('id')->on('asset_assignments')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_returns');
    }
};
