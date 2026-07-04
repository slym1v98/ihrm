<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_template_steps', function (Blueprint $table) {
            $table->string('execution_type', 20)->default('sequential')->after('resolver_config');
            $table->decimal('escalation_sla_hours', 6, 1)->nullable()->after('execution_type');
            $table->string('escalation_target_type', 40)->nullable()->after('escalation_sla_hours');
            $table->jsonb('escalation_target_config')->nullable()->after('escalation_target_type');
        });
    }

    public function down(): void
    {
        Schema::table('workflow_template_steps', function (Blueprint $table) {
            $table->dropColumn(['execution_type', 'escalation_sla_hours', 'escalation_target_type', 'escalation_target_config']);
        });
    }
};
