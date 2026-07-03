<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::create('training_courses', function (Blueprint $t) { $t->uuid('id')->primary(); $t->string('code', 100)->unique(); $t->string('name'); $t->text('description')->nullable(); $t->string('category')->nullable(); $t->integer('default_duration_hours')->nullable(); $t->integer('max_participants')->nullable(); $t->boolean('active')->default(true); $t->timestamps(); }); }
    public function down(): void { Schema::dropIfExists('training_courses'); }
};
