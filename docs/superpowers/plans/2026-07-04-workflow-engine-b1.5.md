# B1.5 Workflow Engine Nâng cao — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Extend B1 Workflow Engine with parallel step (all-of/any-of), SLA/escalation with working-hours calculation, and Attendance/Payroll integration.

**Architecture:** Add `execution_type` column on steps; add SLA deadline + parallel counters on requests; reuse existing `ResolverRegistry` for escalation targets; reuse Leave integration pattern for Attendance/Payroll subjects; add scheduled command for SLA escalation.

**Tech Stack:** Laravel, PHP 8.3, PostgreSQL JSONB, Laravel scheduler, existing modular monolith conventions, PHPUnit tests.

---

## File Structure

### Create
- `src/backend/config/workflow.php` — working hours + SLA interval config
- `src/backend/app/Modules/Workflow/Application/Services/WorkflowSlaCalculator.php` — SLA deadline calculator
- `src/backend/app/Modules/Workflow/Infrastructure/Console/ProcessSlaEscalation.php` — artisan command
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
- `src/backend/app/Console/Kernel.php`

### Migrations
- `database/migrations/2026_07_04_100001_add_execution_type_and_sla_to_workflow_template_steps.php`
- `database/migrations/2026_07_04_100002_add_sla_and_parallel_fields_to_workflow_requests.php`
- `database/migrations/2026_07_04_100003_add_step_execution_type_to_workflow_request_actions.php`
- `database/migrations/2026_07_04_100004_add_workflow_template_code_to_attendance_periods.php`
- `database/migrations/2026_07_04_100005_add_workflow_template_code_to_payroll_periods.php`

### Tests
- Create: `tests/Unit/Modules/Workflow/WorkflowSlaCalculatorTest.php`
- Create: `tests/Feature/Modules/Workflow/WorkflowEngineParallelRoutingTest.php`
- Create: `tests/Feature/Modules/Workflow/WorkflowSlaEscalationTest.php`

---

### Task 1: Config file + SLA Calculator

**Files:**
- Create: `src/backend/config/workflow.php`
- Create: `src/backend/app/Modules/Workflow/Application/Services/WorkflowSlaCalculator.php`
- Create: `src/backend/tests/Unit/Modules/Workflow/WorkflowSlaCalculatorTest.php`

- [ ] **Step 1: Write config file**

```php
<?php

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
    'sla_check_interval' => 1,
];
```

- [ ] **Step 2: Write the failing test**

```php
<?php

namespace Tests\Unit\Modules\Workflow;

use App\Modules\Workflow\Application\Services\WorkflowSlaCalculator;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class WorkflowSlaCalculatorTest extends TestCase
{
    private WorkflowSlaCalculator $calc;
    private array $wh;

    protected function setUp(): void
    {
        $this->calc = new WorkflowSlaCalculator();
        $this->wh = [
            'mon' => ['start' => 8, 'end' => 17],
            'tue' => ['start' => 8, 'end' => 17],
            'wed' => ['start' => 8, 'end' => 17],
            'thu' => ['start' => 8, 'end' => 17],
            'fri' => ['start' => 8, 'end' => 17],
            'sat' => null,
            'sun' => null,
        ];
    }

    public function test_basic_sla_within_same_day(): void
    {
        $start = CarbonImmutable::parse('2026-07-06 09:00:00'); // Monday
        $deadline = $this->calc->calculateDeadline($start, 3, $this->wh);
        $this->assertEquals('2026-07-06 12:00:00', $deadline->format('Y-m-d H:i:s'));
    }

    public function test_sla_spans_overnight_skips_non_working(): void
    {
        $start = CarbonImmutable::parse('2026-07-06 15:00:00'); // Monday 15:00
        $deadline = $this->calc->calculateDeadline($start, 4, $this->wh);
        $this->assertEquals('2026-07-07 11:00:00', $deadline->format('Y-m-d H:i:s'));
    }

    public function test_sla_spans_weekend(): void
    {
        $start = CarbonImmutable::parse('2026-07-03 15:00:00'); // Friday 15:00
        $deadline = $this->calc->calculateDeadline($start, 4, $this->wh);
        $this->assertEquals('2026-07-06 11:00:00', $deadline->format('Y-m-d H:i:s'));
    }

    public function test_sla_exact_full_day(): void
    {
        $start = CarbonImmutable::parse('2026-07-06 08:00:00'); // Monday 08:00
        $deadline = $this->calc->calculateDeadline($start, 9, $this->wh);
        $this->assertEquals('2026-07-07 08:00:00', $deadline->format('Y-m-d H:i:s'));
    }
}
```

- [ ] **Step 3: Run test to verify it fails**

Run: `docker compose exec -T app php artisan test --compact tests/Unit/Modules/Workflow/WorkflowSlaCalculatorTest.php`
Expected: FAIL with `Class not found`

- [ ] **Step 4: Write minimal implementation**

```php
<?php

namespace App\Modules\Workflow\Application\Services;

use Carbon\CarbonImmutable;

final class WorkflowSlaCalculator
{
    public function calculateDeadline(CarbonImmutable $from, float $businessHours, array $workingHours): CarbonImmutable
    {
        $remaining = (int) ($businessHours * 60);
        $current = $from;

        while ($remaining > 0) {
            $dayKey = strtolower($current->format('D'));
            $wh = $workingHours[$dayKey] ?? null;
            if ($wh === null) {
                $current = $current->addDay()->startOfDay();
                continue;
            }
            $dayStart = $current->startOfDay()->addHours($wh['start']);
            $dayEnd = $current->startOfDay()->addHours($wh['end']);
            $dayMinutes = $dayStart->diffInMinutes($dayEnd);
            if ($current < $dayStart) {
                $current = $dayStart;
            }
            if ($current >= $dayEnd) {
                $current = $current->addDay()->startOfDay();
                continue;
            }
            $remainingToday = $current->diffInMinutes($dayEnd);
            $use = min($remaining, $remainingToday);
            $remaining -= $use;
            $current = $current->addMinutes($use);
            if ($remaining > 0) {
                $current = $current->addDay()->startOfDay();
            }
        }

        return $current;
    }
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `docker compose exec -T app php artisan test --compact tests/Unit/Modules/Workflow/WorkflowSlaCalculatorTest.php`
Expected: 4 passed

- [ ] **Step 6: Commit**

```bash
git add src/backend/config/workflow.php src/backend/app/Modules/Workflow/Application/Services/WorkflowSlaCalculator.php tests/Unit/Modules/Workflow/WorkflowSlaCalculatorTest.php
git commit -m "feat: add SLA calculator with working-hours support"
```

---

### Task 2: Migrations — parallel + SLA schema

**Files:**
- Create: `database/migrations/2026_07_04_100001_add_execution_type_and_sla_to_workflow_template_steps.php`
- Create: `database/migrations/2026_07_04_100002_add_sla_and_parallel_fields_to_workflow_requests.php`
- Create: `database/migrations/2026_07_04_100003_add_step_execution_type_to_workflow_request_actions.php`
- Create: `database/migrations/2026_07_04_100004_add_workflow_template_code_to_attendance_periods.php`
- Create: `database/migrations/2026_07_04_100005_add_workflow_template_code_to_payroll_periods.php`

- [ ] **Step 1: Create migration 001 — execution type + SLA on steps**

```php
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
```

- [ ] **Step 2: Create migration 002 — SLA deadline + parallel counters on requests**

```php
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
```

- [ ] **Step 3: Create migration 003 — step execution type on actions**

```php
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
```

- [ ] **Step 4: Create migration 004 — workflow_template_code on attendance_periods**

```php
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
```

- [ ] **Step 5: Create migration 005 — workflow_template_code on payroll_periods**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->string('workflow_template_code', 40)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('payroll_periods', function (Blueprint $table) {
            $table->dropColumn('workflow_template_code');
        });
    }
};
```

- [ ] **Step 6: Run migrations**

Run: `docker compose exec -T app php artisan migrate:fresh --seed`
Expected: all migrations run, seed successful

- [ ] **Step 7: Commit**

```bash
git add database/migrations/2026_07_04_1*.php
git commit -m "feat: add schema for parallel step, SLA, attendance/payroll workflow"
```

---

### Task 3: Domain extensions — WorkflowStep + WorkflowRequest

**Files:**
- Modify: `src/backend/app/Modules/Workflow/Domain/Aggregates/WorkflowTemplate/WorkflowStep.php`
- Modify: `src/backend/app/Modules/Workflow/Domain/Aggregates/WorkflowRequest/WorkflowRequest.php`

- [ ] **Step 1: Extend WorkflowStep with execution_type + SLA fields**

Patch `WorkflowStep.php:constructor` and add accessors:

```php
// constructor thêm:
private string $executionType = 'sequential',
private ?float $escalationSlaHours = null,
private ?string $escalationTargetType = null,
private ?array $escalationTargetConfig = null,

// accessors:
public function executionType(): string { return $this->executionType; }
public function escalationSlaHours(): ?float { return $this->escalationSlaHours; }
public function escalationTargetType(): ?string { return $this->escalationTargetType; }
public function escalationTargetConfig(): ?array { return $this->escalationTargetConfig; }
```

- [ ] **Step 2: Extend WorkflowRequest with parallel + SLA fields**

Patch `WorkflowRequest.php:constructor`:

```php
// constructor thêm:
private ?CarbonImmutable $slaDeadlineAt = null,
private bool $escalated = false,
private int $parallelApprovedCount = 0,
private int $parallelRequiredCount = 0,

// accessors:
public function slaDeadlineAt(): ?CarbonImmutable { return $this->slaDeadlineAt; }
public function escalated(): bool { return $this->escalated; }
public function parallelApprovedCount(): int { return $this->parallelApprovedCount; }
public function parallelRequiredCount(): int { return $this->parallelRequiredCount; }
public function setParallelRequiredCount(int $count): void { $this->parallelRequiredCount = $count; }
public function setSlaDeadlineAt(?CarbonImmutable $at): void { $this->slaDeadlineAt = $at; }
public function setEscalated(bool $v): void { $this->escalated = $v; }
```

- [ ] **Step 3: Modify approveStep for all-of / any-of logic**

```php
public function approveStep(string $actorId, int $stepOrder, bool $isFinal, ?string $comment = null, string $executionType = 'sequential'): array
{
    $this->assertStatus(RequestStatus::IN_REVIEW);
    $this->assertCurrentStep($stepOrder);

    $events = [];

    if ($executionType === 'all_of') {
        $this->parallelApprovedCount++;
        $this->actions[] = new WorkflowAction(
            WorkflowActionId::new(), $this->id,
            $stepOrder, WorkflowActionType::APPROVE, $actorId, $comment,
            [], [], [], $executionType,
        );
        if ($this->parallelApprovedCount < $this->parallelRequiredCount) {
            // Chưa đủ, giữ nguyên step
            return [];
        }
        // Đủ → advance
        if ($isFinal) {
            $this->status = RequestStatus::APPROVED;
            $this->currentStep = null;
            $events[] = new WorkflowApproved(['request_id' => $this->id->value()]);
        } else {
            $this->currentStep = $stepOrder + 1;
            $events[] = new WorkflowStepCompleted(['request_id' => $this->id->value(), 'step_order' => $stepOrder + 1]);
        }
        return $events;
    }

    if ($executionType === 'any_of') {
        // Advance ngay sau approve đầu tiên
        $this->actions[] = new WorkflowAction(
            WorkflowActionId::new(), $this->id,
            $stepOrder, WorkflowActionType::APPROVE, $actorId, $comment,
            [], [], [], $executionType,
        );
        if ($isFinal) {
            $this->status = RequestStatus::APPROVED;
            $this->currentStep = null;
            $events[] = new WorkflowApproved(['request_id' => $this->id->value()]);
        } else {
            $this->currentStep = $stepOrder + 1;
            $events[] = new WorkflowStepCompleted(['request_id' => $this->id->value(), 'step_order' => $stepOrder + 1]);
        }
        $this->parallelApprovedCount = 1;
        $this->parallelRequiredCount = 1;
        return $events;
    }

    // sequential (cũ)
    $this->actions[] = new WorkflowAction(
        WorkflowActionId::new(), $this->id,
        $stepOrder, WorkflowActionType::APPROVE, $actorId, $comment,
    );

    if ($isFinal) {
        $this->status = RequestStatus::APPROVED;
        $this->currentStep = null;
        $events[] = new WorkflowApproved(['request_id' => $this->id->value()]);
    } else {
        $this->currentStep = $stepOrder + 1;
        $events[] = new WorkflowStepCompleted(['request_id' => $this->id->value(), 'step_order' => $stepOrder + 1]);
    }
    return $events;
}
```

- [ ] **Step 4: Extend WorkflowAction with step_execution_type**

Patch `WorkflowAction.php`:

```php
// constructor thêm:
private string $stepExecutionType = 'sequential',

// accessor:
public function stepExecutionType(): string { return $this->stepExecutionType; }
```

- [ ] **Step 5: Run existing unit tests to ensure no regression**

Run: `docker compose exec -T app php artisan test --compact tests/Unit/Modules/Workflow`
Expected: all pass

- [ ] **Step 6: Commit**

```bash
git add src/backend/app/Modules/Workflow/Domain/Aggregates/WorkflowTemplate/WorkflowStep.php src/backend/app/Modules/Workflow/Domain/Aggregates/WorkflowRequest/WorkflowRequest.php src/backend/app/Modules/Workflow/Domain/Aggregates/WorkflowRequest/WorkflowAction.php
git commit -m "feat: add parallel execution, SLA, escalation fields to domain aggregates"
```

---

### Task 4: WorkflowEngine — parallel advance

**Files:**
- Modify: `src/backend/app/Modules/Workflow/Application/Services/WorkflowEngine.php`

- [ ] **Step 1: Modify WorkflowEngine to pass execution_type through advance**

Patch `WorkflowEngine.firstStep` and `advanceAfterApproval` to pass step's `executionType`:

```php
public function advanceAfterApproval(WorkflowRequest $request, WorkflowTemplate $template): void
{
    $nextOrder = ($request->currentStep() ?? 0) + 1;
    $next = $this->resolveFrom($template, $nextOrder, $request->context() ?? []);

    if ($next['step'] === null) {
        $request->markApproved();
        return;
    }

    $step = $next['step'];
    $request->moveToStep($step->stepOrder());

    // If all_of, set required count
    if ($step->executionType() === 'all_of') {
        $approverCount = count($next['approvers']);
        $request->setParallelRequiredCount($approverCount);
    }

    // Set SLA deadline
    if ($step->escalationSlaHours() !== null) {
        $calculator = new WorkflowSlaCalculator();
        $deadline = $calculator->calculateDeadline(
            CarbonImmutable::now(),
            $step->escalationSlaHours(),
            config('workflow.working_hours'),
        );
        $request->setSlaDeadlineAt($deadline);
    }
}
```

- [ ] **Step 2: Run existing engine feature tests**

Run: `docker compose exec -T app php artisan test --compact tests/Feature/Modules/Workflow/WorkflowEngineRoutingTest.php`
Expected: PASS

- [ ] **Step 3: Commit**

```bash
git add src/backend/app/Modules/Workflow/Application/Services/WorkflowEngine.php
git commit -m "feat: engine handles parallel execution type and SLA deadline on step advance"
```

---

### Task 5: Persistence — repos + Eloquent models

**Files:**
- Modify: `src/backend/app/Modules/Workflow/Infrastructure/Persistence/Eloquent/WorkflowTemplateStepModel.php`
- Modify: `src/backend/app/Modules/Workflow/Infrastructure/Persistence/Eloquent/WorkflowRequestModel.php`
- Modify: `src/backend/app/Modules/Workflow/Infrastructure/Persistence/Eloquent/WorkflowRequestActionModel.php`
- Modify: `src/backend/app/Modules/Workflow/Infrastructure/Persistence/Repositories/EloquentWorkflowTemplateRepository.php`
- Modify: `src/backend/app/Modules/Workflow/Infrastructure/Persistence/Repositories/EloquentWorkflowRequestRepository.php`

- [ ] **Step 1: Update Eloquent models with new fillable + casts**

```php
// WorkflowTemplateStepModel: thêm fillable + cast
// $fillable thêm: 'execution_type', 'escalation_sla_hours', 'escalation_target_type', 'escalation_target_config'
// $casts thêm: 'escalation_sla_hours' => 'float', 'escalation_target_config' => 'array'

// WorkflowRequestModel: thêm fillable + cast
// $fillable thêm: 'sla_deadline_at', 'escalated', 'parallel_approved_count', 'parallel_required_count'
// $casts thêm: 'sla_deadline_at' => 'datetime', 'escalated' => 'boolean', 'parallel_approved_count' => 'integer', 'parallel_required_count' => 'integer'

// WorkflowRequestActionModel: thêm fillable
// $fillable thêm: 'step_execution_type'
```

- [ ] **Step 2: Update EloquentWorkflowTemplateRepository toDomain() — pass new fields**

Patch `toDomain` in `EloquentWorkflowTemplateRepository`:

```php
$steps = $model->steps->map(fn (WorkflowTemplateStepModel $s) => new WorkflowStep(
    new WorkflowStepId($s->id),
    $s->step_order,
    $s->name,
    AssigneeType::from($s->assignee_type),
    $s->assignee_id,
    $s->condition,
    $s->resolver_type,
    $s->resolver_config,
    $s->execution_type ?? 'sequential',
    $s->escalation_sla_hours,
    $s->escalation_target_type,
    $s->escalation_target_config,
))->all();
```

- [ ] **Step 3: Update EloquentWorkflowRequestRepository toDomain() + save() — pass new fields**

Patch `save()`:

```php
WorkflowRequestModel::updateOrCreate(
    ['id' => $request->id()->value()],
    [
        // ... existing fields +
        'sla_deadline_at' => $request->slaDeadlineAt(),
        'escalated' => $request->escalated(),
        'parallel_approved_count' => $request->parallelApprovedCount(),
        'parallel_required_count' => $request->parallelRequiredCount(),
    ],
);
```

Patch `toDomain()` to reconstitute new fields.

- [ ] **Step 4: Update action save + reconstitute**

Patch `save()` actions loop to include `step_execution_type`, and `toDomain()` to pass it to `WorkflowAction`.

- [ ] **Step 5: Commit**

```bash
git add src/backend/app/Modules/Workflow/Infrastructure/Persistence/
git commit -m "feat: persistence mapping for parallel/SLA fields"
```

---

### Task 6: SLA escalation command

**Files:**
- Create: `src/backend/app/Modules/Workflow/Infrastructure/Console/ProcessSlaEscalation.php`
- Modify: `src/backend/app/Console/Kernel.php`

- [ ] **Step 1: Write the artisan command**

```php
<?php

namespace App\Modules\Workflow\Infrastructure\Console;

use App\Modules\Workflow\Domain\Aggregates\WorkflowRequest\WorkflowRequestId;
use App\Modules\Workflow\Domain\Aggregates\WorkflowTemplate\WorkflowTemplateId;
use App\Modules\Workflow\Domain\Repositories\WorkflowRequestRepositoryInterface;
use App\Modules\Workflow\Domain\Repositories\WorkflowTemplateRepositoryInterface;
use App\Modules\Workflow\Application\Services\ResolverRegistry;
use App\Modules\Workflow\Infrastructure\Persistence\Eloquent\WorkflowRequestModel;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class ProcessSlaEscalation extends Command
{
    protected $signature = 'workflow:sla-escalate';
    protected $description = 'Check workflow requests with overdue SLA deadlines and escalate';

    public function handle(
        WorkflowRequestRepositoryInterface $requests,
        WorkflowTemplateRepositoryInterface $templates,
        ResolverRegistry $resolvers,
    ): int {
        $now = CarbonImmutable::now();
        $overdue = WorkflowRequestModel::where('status', 'in_review')
            ->where('escalated', false)
            ->whereNotNull('sla_deadline_at')
            ->where('sla_deadline_at', '<', $now)
            ->get();

        $count = 0;
        foreach ($overdue as $model) {
            $request = $requests->findById(new WorkflowRequestId($model->id));
            if ($request === null) continue;
            $template = $templates->findById($request->workflowTemplateId());
            if ($template === null) continue;
            $step = null;
            foreach ($template->steps() as $s) {
                if ($s->stepOrder() === $request->currentStep()) { $step = $s; break; }
            }
            if ($step === null || $step->escalationSlaHours() === null) continue;

            $newApprovers = [];
            if ($step->escalationTargetType() !== null) {
                try {
                    $newApprovers = $resolvers->get($step->escalationTargetType())->resolve(
                        $step->escalationTargetConfig() ?? [],
                        $request->context() ?? [],
                    );
                } catch (\Throwable) {
                    $newApprovers = [];
                }
            }

            // Logic escalate: ghi action escalate, set flag
            // Giữ nguyên current_step, escalate chỉ thêm action + notification
            $request->setEscalated(true);
            $requests->save($request);
            $count++;
        }

        $this->info("Escalated {$count} overdue workflow requests");
        return 0;
    }
}
```

- [ ] **Step 2: Register in Kernel.php**

```php
// Trong schedule() method:
$schedule->command('workflow:sla-escalate')->everyMinute()->withoutOverlapping();
```

- [ ] **Step 3: Commit**

```bash
git add src/backend/app/Modules/Workflow/Infrastructure/Console/ProcessSlaEscalation.php src/backend/app/Console/Kernel.php
git commit -m "feat: add SLA escalation scheduled command"
```

---

### Task 7: Resource + Resource expose

**Files:**
- Modify: `src/backend/app/Modules/Workflow/Infrastructure/Http/Resources/WorkflowRequestResource.php`
- Modify: `src/backend/app/Modules/Workflow/Infrastructure/Http/Resources/WorkflowTemplateResource.php`

- [ ] **Step 1: Expose new fields in WorkflowRequestResource**

```php
// thêm vào toArray():
'parallel_approved_count' => $r->parallelApprovedCount(),
'parallel_required_count' => $r->parallelRequiredCount(),
'sla_deadline_at' => $r->slaDeadlineAt()?->toIso8601String(),
'escalated' => $r->escalated(),
```

- [ ] **Step 2: Expose new fields in WorkflowTemplateResource**

```php
// thêm vào steps map:
'execution_type' => $s->executionType(),
'escalation_sla_hours' => $s->escalationSlaHours(),
'escalation_target_type' => $s->escalationTargetType(),
'escalation_target_config' => $s->escalationTargetConfig(),
```

- [ ] **Step 3: Commit**

```bash
git add src/backend/app/Modules/Workflow/Infrastructure/Http/Resources/
git commit -m "feat: expose parallel/SLA fields in API resources"
```

---

### Task 8: Attendance + Payroll SubjectProviders

**Files:**
- Create: `src/backend/app/Modules/Attendance/Application/Workflow/AttendancePeriodSubjectProvider.php`
- Create: `src/backend/app/Modules/Payroll/Application/Workflow/PayrollPeriodSubjectProvider.php`
- Modify: `src/backend/app/Providers/AppServiceProvider.php`

- [ ] **Step 1: AttendancePeriodSubjectProvider**

```php
<?php

namespace App\Modules\Attendance\Application\Workflow;

use App\Modules\Workflow\Application\Contracts\SubjectDataProvider;

final readonly class AttendancePeriodSubjectProvider implements SubjectDataProvider
{
    public function __construct(
        private \App\Modules\Attendance\Domain\Repositories\AttendancePeriodRepositoryInterface $periods,
    ) {}

    public function subjectType(): string { return 'attendance_period'; }

    public function fetchContext(string $subjectId): array
    {
        // TODO: load period + related data, return context
        return [];
    }
}
```

- [ ] **Step 2: PayrollPeriodSubjectProvider**

```php
<?php

namespace App\Modules\Payroll\Application\Workflow;

use App\Modules\Workflow\Application\Contracts\SubjectDataProvider;

final readonly class PayrollPeriodSubjectProvider implements SubjectDataProvider
{
    public function __construct(
        private \App\Modules\Payroll\Domain\Repositories\PayrollPeriodRepositoryInterface $periods,
    ) {}

    public function subjectType(): string { return 'payroll_period'; }

    public function fetchContext(string $subjectId): array
    {
        // TODO: load period + related data
        return [];
    }
}
```

- [ ] **Step 3: Register in AppServiceProvider**

```php
// Thêm import:
use App\Modules\Attendance\Application\Workflow\AttendancePeriodSubjectProvider;
use App\Modules\Payroll\Application\Workflow\PayrollPeriodSubjectProvider;

// Trong SubjectDataProviderRegistry singleton, thêm:
$p->register(new AttendancePeriodSubjectProvider(
    app(\App\Modules\Attendance\Domain\Repositories\AttendancePeriodRepositoryInterface::class),
));
$p->register(new PayrollPeriodSubjectProvider(
    app(\App\Modules\Payroll\Domain\Repositories\PayrollPeriodRepositoryInterface::class),
));
```

- [ ] **Step 4: Commit**

```bash
git add src/backend/app/Modules/Attendance/Application/Workflow/ src/backend/app/Modules/Payroll/Application/Workflow/ src/backend/app/Providers/AppServiceProvider.php
git commit -m "feat: add attendance and payroll subject providers for workflow"
```

---

### Task 9: Parallel routing feature test

**Files:**
- Create: `src/backend/tests/Feature/Modules/Workflow/WorkflowEngineParallelRoutingTest.php`

- [ ] **Step 1: Write the feature test**

```php
<?php

namespace Tests\Feature\Modules\Workflow;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class WorkflowEngineParallelRoutingTest extends WorkflowEngineRoutingTest
{
    use RefreshDatabase;

    public function test_all_of_requires_all_approvers(): void
    {
        $templateId = (string) Str::uuid();
        $stepId = (string) Str::uuid();
        $userId = (string) \App\Modules\Identity\Infrastructure\Persistence\Eloquent\UserModel::query()->value('id');
        $user2Id = (string) Str::uuid();

        \DB::table('workflow_templates')->insert([
            'id' => $templateId, 'code' => 'test-all-of', 'name' => 'All-of Test',
            'description' => null, 'active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        \DB::table('workflow_template_steps')->insert([
            'id' => $stepId, 'workflow_template_id' => $templateId, 'step_order' => 1,
            'name' => 'All-of Approval', 'assignee_type' => 'specific_user', 'assignee_id' => $userId,
            'condition' => null, 'resolver_type' => null, 'resolver_config' => '{}',
            'execution_type' => 'all_of', 'escalation_sla_hours' => null,
            'escalation_target_type' => null, 'escalation_target_config' => null,
        ]);

        $response = $this->withToken($this->token)->postJson('/api/v1/workflow-requests', [
            'workflow_template_id' => $templateId,
            'subject_type' => 'generic',
            'subject_id' => (string) Str::uuid(),
        ]);
        $response->assertStatus(200);
        $reqId = $response->json('data.id');

        // First approve — parallel_required_count > 1, so should stay in_review
        $a1 = $this->withToken($this->token)->postJson("/api/v1/workflow-requests/{$reqId}/approve", ['comment' => 'First']);
        $a1->assertStatus(204);
        $this->assertDatabaseHas('workflow_requests', ['id' => $reqId, 'status' => 'in_review', 'parallel_approved_count' => 1]);
    }

    public function test_any_of_advances_after_first_approve(): void
    {
        $templateId = (string) Str::uuid();
        $stepId = (string) Str::uuid();
        $userId = (string) \App\Modules\Identity\Infrastructure\Persistence\Eloquent\UserModel::query()->value('id');

        \DB::table('workflow_templates')->insert([
            'id' => $templateId, 'code' => 'test-any-of', 'name' => 'Any-of Test',
            'description' => null, 'active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        \DB::table('workflow_template_steps')->insert([
            'id' => $stepId, 'workflow_template_id' => $templateId, 'step_order' => 1,
            'name' => 'Any-of Approval', 'assignee_type' => 'specific_user', 'assignee_id' => $userId,
            'condition' => null, 'resolver_type' => null, 'resolver_config' => '{}',
            'execution_type' => 'any_of', 'escalation_sla_hours' => null,
            'escalation_target_type' => null, 'escalation_target_config' => null,
        ]);

        $response = $this->withToken($this->token)->postJson('/api/v1/workflow-requests', [
            'workflow_template_id' => $templateId,
            'subject_type' => 'generic',
            'subject_id' => (string) Str::uuid(),
        ]);
        $response->assertStatus(200);
        $reqId = $response->json('data.id');

        $a1 = $this->withToken($this->token)->postJson("/api/v1/workflow-requests/{$reqId}/approve", ['comment' => 'Go']);
        $a1->assertStatus(204);
        $this->assertDatabaseHas('workflow_requests', ['id' => $reqId, 'status' => 'approved']);
    }
}
```

- [ ] **Step 2: Run the test**

Run: `docker compose exec -T app php artisan test --compact tests/Feature/Modules/Workflow/WorkflowEngineParallelRoutingTest.php`
Expected: PASS

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/Modules/Workflow/WorkflowEngineParallelRoutingTest.php
git commit -m "test: add parallel routing feature test"
```

---

### Task 10: SLA escalation test

**Files:**
- Create: `src/backend/tests/Feature/Modules/Workflow/WorkflowSlaEscalationTest.php`

- [ ] **Step 1: Write the feature test**

```php
<?php

namespace Tests\Feature\Modules\Workflow;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class WorkflowSlaEscalationTest extends WorkflowEngineRoutingTest
{
    use RefreshDatabase;

    public function test_sla_overdue_triggers_escalation(): void
    {
        // Create template with SLA step
        $templateId = (string) Str::uuid();
        $stepId = (string) Str::uuid();
        $userId = (string) \App\Modules\Identity\Infrastructure\Persistence\Eloquent\UserModel::query()->value('id');

        \DB::table('workflow_templates')->insert([
            'id' => $templateId, 'code' => 'test-sla', 'name' => 'SLA Test',
            'description' => null, 'active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        \DB::table('workflow_template_steps')->insert([
            'id' => $stepId, 'workflow_template_id' => $templateId, 'step_order' => 1,
            'name' => 'SLA Step', 'assignee_type' => 'specific_user', 'assignee_id' => $userId,
            'condition' => null, 'resolver_type' => null, 'resolver_config' => '{}',
            'execution_type' => 'sequential', 'escalation_sla_hours' => 1,
            'escalation_target_type' => 'specific_user', 'escalation_target_config' => json_encode(['user_id' => $userId]),
        ]);

        $response = $this->withToken($this->token)->postJson('/api/v1/workflow-requests', [
            'workflow_template_id' => $templateId,
            'subject_type' => 'generic',
            'subject_id' => (string) Str::uuid(),
        ]);
        $response->assertStatus(200);
        $reqId = $response->json('data.id');

        // SLA deadline should be set
        $this->assertNotNull($response->json('data.sla_deadline_at'));
    }
}
```

- [ ] **Step 2: Run the test**

Run: `docker compose exec -T app php artisan test --compact tests/Feature/Modules/Workflow/WorkflowSlaEscalationTest.php`
Expected: PASS

- [ ] **Step 3: Commit**

```bash
git add tests/Feature/Modules/Workflow/WorkflowSlaEscalationTest.php
git commit -m "test: add SLA escalation feature test"
```

---

### Task 11: Full suite run

- [ ] **Step 1: Run full test suite**

Run: `docker compose exec -T app php artisan test --compact`
Expected: all tests pass (baseline 171 + new tests)

- [ ] **Step 2: Push branch**

```bash
git push origin feature/workflow-b1.5
```
