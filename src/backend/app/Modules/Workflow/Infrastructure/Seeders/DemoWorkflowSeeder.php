<?php

namespace App\Modules\Workflow\Infrastructure\Seeders;

use App\Modules\Workflow\Infrastructure\Persistence\Eloquent\WorkflowTemplateModel;
use App\Modules\Workflow\Infrastructure\Persistence\Eloquent\WorkflowTemplateStepModel;
use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class DemoWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        // Leave approval template
        $leaveTemplate = WorkflowTemplateModel::where('code', 'LEAVE-APPROVAL')->first();
        if (! $leaveTemplate) {
            $leaveTemplate = WorkflowTemplateModel::create([
                'id' => (string) Uuid::uuid4(),
                'code' => 'LEAVE-APPROVAL',
                'name' => 'Phê duyệt nghỉ phép',
                'description' => 'Quy trình duyệt đơn nghỉ phép cấp bậc',
                'active' => true,
            ]);

            foreach ([
                ['step_order' => 1, 'name' => 'Trưởng phòng duyệt', 'assignee_type' => 'manager', 'resolver_type' => 'manager_of_submitter', 'execution_type' => 'sequential'],
                ['step_order' => 2, 'name' => 'HR duyệt', 'assignee_type' => 'role', 'resolver_type' => 'role', 'resolver_config' => ['role' => 'hr'], 'execution_type' => 'sequential'],
            ] as $s) {
                WorkflowTemplateStepModel::create(array_merge($s, [
                    'id' => (string) Uuid::uuid4(),
                    'workflow_template_id' => $leaveTemplate->id,
                    'resolver_config' => $s['resolver_config'] ?? [],
                ]));
            }
        }

        // Payroll approval template
        $payrollTemplate = WorkflowTemplateModel::where('code', 'PAYROLL-APPROVAL')->first();
        if (! $payrollTemplate) {
            $payrollTemplate = WorkflowTemplateModel::create([
                'id' => (string) Uuid::uuid4(),
                'code' => 'PAYROLL-APPROVAL',
                'name' => 'Phê duyệt bảng lương',
                'description' => 'Quy trình duyệt kỳ lương',
                'active' => true,
            ]);

            foreach ([
                ['step_order' => 1, 'name' => 'Kế toán trưởng', 'assignee_type' => 'role', 'resolver_type' => 'role', 'resolver_config' => ['role' => 'accounting'], 'execution_type' => 'sequential'],
                ['step_order' => 2, 'name' => 'Giám đốc', 'assignee_type' => 'role', 'resolver_type' => 'role', 'resolver_config' => ['role' => 'director'], 'execution_type' => 'sequential'],
            ] as $s) {
                WorkflowTemplateStepModel::create(array_merge($s, [
                    'id' => (string) Uuid::uuid4(),
                    'workflow_template_id' => $payrollTemplate->id,
                    'resolver_config' => $s['resolver_config'] ?? [],
                ]));
            }
        }
    }
}
