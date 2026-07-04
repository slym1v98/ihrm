<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_template_steps', function (Blueprint $table) {
            $table->jsonb('form_schema')->nullable()->after('escalation_target_config');
        });
        Schema::table('workflow_request_actions', function (Blueprint $table) {
            $table->jsonb('form_data')->nullable()->after('step_execution_type');
        });
    }

    public function down(): void
    {
        Schema::table('workflow_request_actions', function (Blueprint $table) {
            $table->dropColumn('form_data');
        });
        Schema::table('workflow_template_steps', function (Blueprint $table) {
            $table->dropColumn('form_schema');
        });
    }
};
