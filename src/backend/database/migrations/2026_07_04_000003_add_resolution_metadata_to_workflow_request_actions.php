<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_request_actions', function (Blueprint $table) {
            $table->jsonb('resolved_approvers')->default('[]')->after('metadata');
            $table->jsonb('delegation_map')->default('{}')->after('resolved_approvers');
        });
    }

    public function down(): void
    {
        Schema::table('workflow_request_actions', function (Blueprint $table) {
            $table->dropColumn(['resolved_approvers', 'delegation_map']);
        });
    }
};
