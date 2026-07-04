# B1.5 Design — Workflow Engine nâng cao (parallel, SLA, Attendance/Payroll)

Ngày: 2026-07-04
Phạm vi: Nhóm B1.5 — Workflow Engine nâng cao
Trạng thái: Draft — chờ user review

## 1. Mục tiêu

Mở rộng Workflow Engine B1 với:

- Parallel step: all-of / any-of execution type
- SLA / timeout / auto escalation với working-hours calculation
- Tích hợp Attendance + Payroll theo pattern Leave đã có

Ngoài phạm vi B1.5:

- Conditional routing động (đã có ở B1)
- Delegation (đã có ở B1)
- Nested step / sub-workflow (→ B2)

## 2. Parallel step — execution_type

### 2.1. Schema

Migration `add_execution_type_to_workflow_template_steps`:

```php
Schema::table('workflow_template_steps', function (Blueprint $table) {
    $table->string('execution_type', 20)->default('sequential')
          ->after('resolver_config');
    $table->decimal('escalation_sla_hours', 6, 1)->nullable()
          ->after('execution_type');
    $table->string('escalation_target_type', 40)->nullable()
          ->after('escalation_sla_hours');
    $table->jsonb('escalation_target_config')->nullable()
          ->after('escalation_target_type');
});
```

Step mặc định `sequential`; `all_of` / `any_of` cho parallel.

Migration `add_deadline_and_counts_to_workflow_requests`:

```php
Schema::table('workflow_requests', function (Blueprint $table) {
    $table->timestamp('sla_deadline_at')->nullable();
    $table->boolean('escalated')->default(false);
    $table->integer('parallel_approved_count')->default(0);
    $table->integer('parallel_required_count')->default(0);
});
```

### 2.2. Domain logic

**`WorkflowStep`** thêm:
- `executionType(): string` — sequential | all_of | any_of
- `escalationSlaHours(): ?float`
- `escalationTargetType(): ?string`
- `escalationTargetConfig(): ?array`

**`WorkflowRequest.approveStep(string $actorId, int $stepOrder, bool $isFinal, ?string $comment)`**:

Logic mới dựa trên execution type của step hiện tại:

- **sequential**: như cũ — ghi action, advance (hoặc mark approved nếu final)
- **all_of**: increment `parallel_approved_count`. Nếu `count >= parallel_required_count` thì advance. Nếu chưa đủ thì giữ nguyên current_step (step vẫn active, chờ các approver khác).
- **any_of**: advance ngay sau approve đầu tiên. Các approve sau cho cùng step → action ghi nhận `step_already_closed` / redundant (engine ignore).

**`WorkflowEngine.advanceAfterApproval`**: check execution type của step vừa approve trước khi decide advance.

### 2.3. Resource expose

`WorkflowRequestResource`: thêm `parallel_approved_count`, `parallel_required_count`, `sla_deadline_at`, `escalated`.

`WorkflowTemplateResource`: thêm `execution_type`, `escalation_sla_hours`, `escalation_target_type`, `escalation_target_config`.

## 3. SLA / Escalation

### 3.1. Working hours config

File `config/workflow.php`:

```php
return [
    'working_hours' => [
        'mon' => ['start' => 8, 'end' => 17],
        'tue' => ['start' => 8, 'end' => 17],
        'wed' => ['start' => 8, 'end' => 17],
        'thu' => ['start' => 8, 'end' => 17],
        'fri' => ['start' => 8, 'end' => 17],
        'sat' => null,
        'sun' => null,
    ],
    'sla_check_interval' => 1, // phút
];
```

### 3.2. SLA calculation service

`WorkflowSlaCalculator` service:

```php
class WorkflowSlaCalculator
{
    public function calculateDeadline(CarbonImmutable $from, float $businessHours, array $workingHours): CarbonImmutable
    {
        // Chỉ tính thời gian trong working hours, bỏ ngoài giờ & cuối tuần
        $remaining = $businessHours * 60; // phút
        $current = $from;
        while ($remaining > 0) {
            $dayName = strtolower($current->format('D'));
            $wh = $workingHours[$dayName] ?? null;
            if ($wh === null) {
                $current = $current->addDay()->startOfDay();
                continue;
            }
            $dayMinutes = ($wh['end'] - $wh['start']) * 60;
            $elapsed = $current->diffInMinutes($current->copy()->startOfDay());
            $available = max(0, $dayMinutes - max(0, $elapsed - $wh['start'] * 60));
            $use = min($remaining, $available);
            $remaining -= $use;
            $current = $current->addMinutes($use);
            if ($remaining > 0) {
                $current = $current->addDay()->startOfDay()->addHours($wh['start']);
            }
        }
        return $current;
    }
}
```

### 3.3. Scheduled job

`App\Modules\Workflow\Infrastructure\Console\ProcessSlaEscalation` — artisan command `workflow:sla-escalate`:

```php
class ProcessSlaEscalation
{
    public function handle(): void
    {
        $now = CarbonImmutable::now();
        $overdue = WorkflowRequestModel::where('status', 'in_review')
            ->where('escalated', false)
            ->where('sla_deadline_at', '<', $now)
            ->get();

        foreach ($overdue as $model) {
            $request = /* reconstitute */;
            $template = /* load template */;
            $step = $template->findStep($request->currentStep());
            if ($step === null || !$step->escalationSlaHours()) continue;

            $newApprovers = $this->resolveEscalationTarget($step);
            // Ghi action escalate
            // Giữ nguyên current_step, chỉ thay đổi assignee (next action sẽ đến từ người mới)
            $request->setEscalated(true);
            $this->requests->save($request);
        }
    }
}
```

Đăng ký trong `Kernel.php`:

```php
$schedule->command('workflow:sla-escalate')->everyMinute();
```

### 3.4. Escalation target resolve

Dùng lại `ResolverRegistry` đã có ở B1 — `escalation_target_type` tương ứng resolver key, `escalation_target_config` là config.

## 4. Attendance + Payroll integration

### 4.1. Schema

Migration `add_workflow_template_code_to_attendance_periods`:

```php
Schema::table('attendance_periods', function (Blueprint $table) {
    $table->string('workflow_template_code', 40)->nullable();
});
```

Migration tương tự cho `payroll_periods` (Payroll có thể cần workflow riêng).

### 4.2. Subject providers

**`AttendancePeriodSubjectProvider`**:

- `subjectType()`: `'attendance_period'`
- `fetchContext(string $subjectId)`: return `employee_id`, `manager_id`, `department_id`, `period_code`, `total_worked_minutes`, `total_overtime_minutes`, `total_late_minutes`, `total_early_leave_minutes`, `total_absent_minutes`

**`PayrollPeriodSubjectProvider`**:

- `subjectType()`: `'payroll_period'`
- `fetchContext(string $subjectId)`: return `employee_id`, `manager_id`, `period_code`, `gross_amount`, `net_amount`, `component_count`

### 4.3. Submit hook

Giống Leave: submit Attendance/Payroll period (hoặc một action như "close period" / "finalize payroll") → handler check `workflow_template_code` → auto-submit `WorkflowRequest`.

### 4.4. Listener sync

`WorkflowApproved` + `WorkflowRejected` → listener update trạng thái attendance/payroll (VD: period `locked`, `approved`, hoặc "cần unlock để chỉnh sửa").

## 5. Không trong scope B1.5

- Nested step / sub-workflow → B2
- Dynamic form per step → B2
- Workflow analytics dashboard → B3
- Tích hợp Recruitment, Offboarding → B4
- Frontend workflow designer → C1

## 6. Migration plan

1. `add_execution_type_to_workflow_template_steps` — thêm 4 cột
2. `add_deadline_and_counts_to_workflow_requests` — thêm 4 cột
3. `add_step_execution_type_to_workflow_request_actions` — metadata cho action
4. `add_workflow_template_code_to_attendance_periods` — 1 cột
5. `add_workflow_template_code_to_payroll_periods` — 1 cột

## 7. Files affected

### Create
- `src/backend/config/workflow.php`
- `src/backend/app/Modules/Workflow/Application/Services/WorkflowSlaCalculator.php`
- `src/backend/app/Modules/Workflow/Infrastructure/Console/ProcessSlaEscalation.php`
- `src/backend/app/Modules/Attendance/Application/Workflow/AttendancePeriodSubjectProvider.php`
- `src/backend/app/Modules/Payroll/Application/Workflow/PayrollPeriodSubjectProvider.php`

### Modify
- `src/backend/app/Modules/Workflow/Domain/Aggregates/WorkflowTemplate/WorkflowStep.php`
- `src/backend/app/Modules/Workflow/Domain/Aggregates/WorkflowRequest/WorkflowRequest.php`
- `src/backend/app/Modules/Workflow/Application/Services/WorkflowEngine.php`
- `src/backend/app/Modules/Workflow/Infrastructure/Persistence/Eloquent/WorkflowTemplateStepModel.php`
- `src/backend/app/Modules/Workflow/Infrastructure/Persistence/Eloquent/WorkflowRequestModel.php`
- `src/backend/app/Modules/Workflow/Infrastructure/Persistence/Eloquent/WorkflowRequestActionModel.php`
- `src/backend/app/Modules/Workflow/Infrastructure/Persistence/Repositories/EloquentWorkflowTemplateRepository.php`
- `src/backend/app/Modules/Workflow/Infrastructure/Persistence/Repositories/EloquentWorkflowRequestRepository.php`
- `src/backend/app/Modules/Workflow/Infrastructure/Http/Resources/WorkflowRequestResource.php`
- `src/backend/app/Modules/Workflow/Infrastructure/Http/Resources/WorkflowTemplateResource.php`
- `src/backend/app/Providers/AppServiceProvider.php`
- `src/backend/app/Console/Kernel.php` (scheduler)

## 8. Tests

- Unit: `SlaCalculatorTest`, `WorkflowParallelStepTest`
- Feature: `WorkflowEngineParallelRoutingTest`, `WorkflowSlaEscalationTest`
- Integration: `AttendanceWorkflowTest`, `PayrollWorkflowTest`
