<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_template_steps', function (Blueprint $table) {
            $table->string('resolver_type', 40)->nullable()->after('assignee_id');
            $table->jsonb('resolver_config')->default('{}')->after('resolver_type');
        });
    }

    public function down(): void
    {
        Schema::table('workflow_template_steps', function (Blueprint $table) {
            $table->dropColumn(['resolver_type', 'resolver_config']);
        });
    }
};
