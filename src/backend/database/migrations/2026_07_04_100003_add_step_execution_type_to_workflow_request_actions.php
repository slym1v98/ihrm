<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_request_actions', function (Blueprint $table) {
            $table->string('step_execution_type', 20)->nullable()->after('delegation_map');
        });
    }

    public function down(): void
    {
        Schema::table('workflow_request_actions', function (Blueprint $table) {
            $table->dropColumn('step_execution_type');
        });
    }
};
