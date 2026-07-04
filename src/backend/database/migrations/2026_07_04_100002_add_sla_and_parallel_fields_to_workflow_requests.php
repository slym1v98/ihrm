<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workflow_requests', function (Blueprint $table) {
            $table->timestamp('sla_deadline_at')->nullable()->after('context');
            $table->boolean('escalated')->default(false)->after('sla_deadline_at');
            $table->integer('parallel_approved_count')->default(0)->after('escalated');
            $table->integer('parallel_required_count')->default(0)->after('parallel_approved_count');
        });
    }

    public function down(): void
    {
        Schema::table('workflow_requests', function (Blueprint $table) {
            $table->dropColumn(['sla_deadline_at', 'escalated', 'parallel_approved_count', 'parallel_required_count']);
        });
    }
};
