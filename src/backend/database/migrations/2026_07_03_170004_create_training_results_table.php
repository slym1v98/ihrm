<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::create('training_results', function (Blueprint $t) { $t->uuid('id')->primary(); $t->uuid('enrollment_id'); $t->decimal('score', 5, 2)->nullable(); $t->boolean('passed')->nullable(); $t->string('certificate_code', 100)->nullable(); $t->dateTime('issued_at')->nullable(); $t->text('notes')->nullable(); $t->timestamps(); $t->foreign('enrollment_id')->references('id')->on('training_enrollments')->cascadeOnDelete(); $t->unique('enrollment_id'); }); }
    public function down(): void { Schema::dropIfExists('training_results'); }
};
