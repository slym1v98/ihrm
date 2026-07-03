<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::create('training_sessions', function (Blueprint $t) { $t->uuid('id')->primary(); $t->uuid('course_id'); $t->string('code', 100); $t->string('name'); $t->dateTime('start_date'); $t->dateTime('end_date'); $t->string('location')->nullable(); $t->string('instructor')->nullable(); $t->integer('max_participants')->nullable(); $t->string('status', 20)->default('scheduled'); $t->timestamps(); $t->foreign('course_id')->references('id')->on('training_courses')->cascadeOnDelete(); $t->index('status'); }); }
    public function down(): void { Schema::dropIfExists('training_sessions'); }
};
