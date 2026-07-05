<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        \DB::statement("CREATE INDEX IF NOT EXISTS attendance_raw_logs_employee_id_event_time_index ON attendance_raw_logs (employee_id, event_time)");
        \DB::statement("CREATE INDEX IF NOT EXISTS attendance_adjustment_requests_employee_id_status_index ON attendance_adjustment_requests (employee_id, status)");
        \DB::statement("CREATE INDEX IF NOT EXISTS attendance_timesheets_employee_id_attendance_period_id_index ON attendance_timesheets (employee_id, attendance_period_id)");
        \DB::statement("CREATE INDEX IF NOT EXISTS leave_requests_employee_id_status_start_at_index ON leave_requests (employee_id, status, start_at)");
        \DB::statement("CREATE INDEX IF NOT EXISTS notification_outbox_status_notification_message_id_available_at_index ON notification_outbox (status, notification_message_id, available_at)");
        \DB::statement("CREATE INDEX IF NOT EXISTS workflow_requests_status_created_at_index ON workflow_requests (status, created_at)");
        \DB::statement("CREATE INDEX IF NOT EXISTS workflow_request_actions_workflow_request_id_actor_id_index ON workflow_request_actions (workflow_request_id, actor_id)");
        \DB::statement("CREATE INDEX IF NOT EXISTS audit_logs_entity_type_entity_id_index ON audit_logs (entity_type, entity_id)");
        \DB::statement("CREATE INDEX IF NOT EXISTS audit_logs_occurred_at_index ON audit_logs (occurred_at)");
        \DB::statement("CREATE INDEX IF NOT EXISTS payroll_entries_run_id_employee_id_index ON payroll_entries (run_id, employee_id)");
        \DB::statement("CREATE INDEX IF NOT EXISTS onboarding_tasks_onboarding_plan_id_owner_type_owner_id_index ON onboarding_tasks (onboarding_plan_id, owner_type, owner_id)");
        \DB::statement("CREATE INDEX IF NOT EXISTS offboarding_tasks_offboarding_plan_id_owner_type_owner_id_index ON offboarding_tasks (offboarding_plan_id, owner_type, owner_id)");
        \DB::statement("CREATE INDEX IF NOT EXISTS shift_assignments_assignable_type_assignable_id_active_index ON shift_assignments (assignable_type, assignable_id, active)");
    }

    public function down(): void
    {
        \DB::statement("DROP INDEX IF EXISTS attendance_raw_logs_employee_id_event_time_index");
        \DB::statement("DROP INDEX IF EXISTS attendance_adjustment_requests_employee_id_status_index");
        \DB::statement("DROP INDEX IF EXISTS attendance_timesheets_employee_id_attendance_period_id_index");
        \DB::statement("DROP INDEX IF EXISTS leave_requests_employee_id_status_start_at_index");
        \DB::statement("DROP INDEX IF EXISTS notification_outbox_status_notification_message_id_available_at_index");
        \DB::statement("DROP INDEX IF EXISTS workflow_requests_status_created_at_index");
        \DB::statement("DROP INDEX IF EXISTS workflow_request_actions_workflow_request_id_actor_id_index");
        \DB::statement("DROP INDEX IF EXISTS audit_logs_entity_type_entity_id_index");
        \DB::statement("DROP INDEX IF EXISTS audit_logs_occurred_at_index");
        \DB::statement("DROP INDEX IF EXISTS payroll_entries_run_id_employee_id_index");
        \DB::statement("DROP INDEX IF EXISTS onboarding_tasks_onboarding_plan_id_owner_type_owner_id_index");
        \DB::statement("DROP INDEX IF EXISTS offboarding_tasks_offboarding_plan_id_owner_type_owner_id_index");
        \DB::statement("DROP INDEX IF EXISTS shift_assignments_assignable_type_assignable_id_active_index");
    }
};