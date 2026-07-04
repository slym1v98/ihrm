<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_periods', function (Blueprint $table) {
            $table->string('workflow_template_code', 40)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('attendance_periods', function (Blueprint $table) {
            $table->dropColumn('workflow_template_code');
        });
    }
};
