# Phase 3 Onboarding Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build Phase 3 Onboarding module with templates, plans, tasks, workflow approval, Recruitment integration, and full test suite.

**Architecture:** Strict DDD 3-layer module under `app/Modules/Onboarding/`. 3 aggregates (template/plan/task). Workflow BC integration for plan completion sign-off and optional task-level approval. Recruitment `CandidateHired` event listener auto-creates plans. Notification via direct calls + events. Eloquent repos, PHPUnit tests.

**Tech Stack:** Laravel 12, PHP 8.4, PostgreSQL 16 UUID/JSONB, Sanctum, Eloquent repos, PHPUnit.

---

## File Map

```
Create: src/backend/database/migrations/2026_07_03_140001_create_onboarding_templates_table.php
Create: src/backend/database/migrations/2026_07_03_140002_create_onboarding_plans_table.php
Create: src/backend/database/migrations/2026_07_03_140003_create_onboarding_tasks_table.php

Create: src/backend/app/Modules/Onboarding/Domain/ValueObjects/OnboardingPlanStatus.php
Create: src/backend/app/Modules/Onboarding/Domain/ValueObjects/OnboardingTaskStatus.php
Create: src/backend/app/Modules/Onboarding/Domain/ValueObjects/TaskType.php
Create: src/backend/app/Modules/Onboarding/Domain/ValueObjects/OwnerType.php
Create: src/backend/app/Modules/Onboarding/Domain/ValueObjects/TemplateRules.php

Create: src/backend/app/Modules/Onboarding/Domain/Exceptions/OnboardingPlanNotFoundException.php
Create: src/backend/app/Modules/Onboarding/Domain/Exceptions/OnboardingTaskNotFoundException.php
Create: src/backend/app/Modules/Onboarding/Domain/Exceptions/OnboardingTemplateNotFoundException.php
Create: src/backend/app/Modules/Onboarding/Domain/Exceptions/InvalidStatusTransitionException.php
Create: src/backend/app/Modules/Onboarding/Domain/Exceptions/MandatoryTaskIncompleteException.php

Create: src/backend/app/Modules/Onboarding/Domain/Events/OnboardingPlanCreated.php
Create: src/backend/app/Modules/Onboarding/Domain/Events/OnboardingPlanActivated.php
Create: src/backend/app/Modules/Onboarding/Domain/Events/OnboardingPlanCompleted.php
Create: src/backend/app/Modules/Onboarding/Domain/Events/OnboardingTaskAssigned.php
Create: src/backend/app/Modules/Onboarding/Domain/Events/OnboardingTaskStarted.php
Create: src/backend/app/Modules/Onboarding/Domain/Events/OnboardingTaskCompleted.php
Create: src/backend/app/Modules/Onboarding/Domain/Events/OnboardingTaskWaived.php
Create: src/backend/app/Modules/Onboarding/Domain/Events/OnboardingCompleted.php

Create: src/backend/app/Modules/Onboarding/Domain/Aggregates/OnboardingTemplate/OnboardingTemplateId.php
Create: src/backend/app/Modules/Onboarding/Domain/Aggregates/OnboardingTemplate/OnboardingTemplate.php
Create: src/backend/app/Modules/Onboarding/Domain/Aggregates/OnboardingPlan/OnboardingPlanId.php
Create: src/backend/app/Modules/Onboarding/Domain/Aggregates/OnboardingPlan/OnboardingPlan.php
Create: src/backend/app/Modules/Onboarding/Domain/Aggregates/OnboardingTask/OnboardingTaskId.php
Create: src/backend/app/Modules/Onboarding/Domain/Aggregates/OnboardingTask/OnboardingTask.php

Create: src/backend/app/Modules/Onboarding/Domain/Repositories/OnboardingTemplateRepositoryInterface.php
Create: src/backend/app/Modules/Onboarding/Domain/Repositories/OnboardingPlanRepositoryInterface.php
Create: src/backend/app/Modules/Onboarding/Domain/Repositories/OnboardingTaskRepositoryInterface.php

Create: src/backend/app/Modules/Onboarding/Application/Commands/CreateOnboardingTemplateCommand.php
Create: src/backend/app/Modules/Onboarding/Application/Commands/UpdateOnboardingTemplateCommand.php
Create: src/backend/app/Modules/Onboarding/Application/Commands/CreateOnboardingPlanCommand.php
Create: src/backend/app/Modules/Onboarding/Application/Commands/ActivateOnboardingPlanCommand.php
Create: src/backend/app/Modules/Onboarding/Application/Commands/CancelOnboardingPlanCommand.php
Create: src/backend/app/Modules/Onboarding/Application/Commands/CompleteOnboardingPlanCommand.php
Create: src/backend/app/Modules/Onboarding/Application/Commands/AddOnboardingTaskCommand.php
Create: src/backend/app/Modules/Onboarding/Application/Commands/RemoveOnboardingTaskCommand.php
Create: src/backend/app/Modules/Onboarding/Application/Commands/StartTaskCommand.php
Create: src/backend/app/Modules/Onboarding/Application/Commands/CompleteTaskCommand.php
Create: src/backend/app/Modules/Onboarding/Application/Commands/WaiveTaskCommand.php

Create: src/backend/app/Modules/Onboarding/Application/CommandHandlers/CreateOnboardingTemplateHandler.php
Create: src/backend/app/Modules/Onboarding/Application/CommandHandlers/UpdateOnboardingTemplateHandler.php
Create: src/backend/app/Modules/Onboarding/Application/CommandHandlers/CreateOnboardingPlanHandler.php
Create: src/backend/app/Modules/Onboarding/Application/CommandHandlers/ActivateOnboardingPlanHandler.php
Create: src/backend/app/Modules/Onboarding/Application/CommandHandlers/CancelOnboardingPlanHandler.php
Create: src/backend/app/Modules/Onboarding/Application/CommandHandlers/CompleteOnboardingPlanHandler.php
Create: src/backend/app/Modules/Onboarding/Application/CommandHandlers/AddOnboardingTaskHandler.php
Create: src/backend/app/Modules/Onboarding/Application/CommandHandlers/RemoveOnboardingTaskHandler.php
Create: src/backend/app/Modules/Onboarding/Application/CommandHandlers/StartTaskHandler.php
Create: src/backend/app/Modules/Onboarding/Application/CommandHandlers/CompleteTaskHandler.php
Create: src/backend/app/Modules/Onboarding/Application/CommandHandlers/WaiveTaskHandler.php

Create: src/backend/app/Modules/Onboarding/Application/Queries/ListPlansQuery.php
Create: src/backend/app/Modules/Onboarding/Application/Queries/ListTemplatesQuery.php
Create: src/backend/app/Modules/Onboarding/Application/Queries/ListTasksQuery.php
Create: src/backend/app/Modules/Onboarding/Application/QueryHandlers/ListPlansHandler.php
Create: src/backend/app/Modules/Onboarding/Application/QueryHandlers/ListTemplatesHandler.php
Create: src/backend/app/Modules/Onboarding/Application/QueryHandlers/ListTasksHandler.php

Create: src/backend/app/Modules/Onboarding/Infrastructure/Persistence/Eloquent/OnboardingTemplateModel.php
Create: src/backend/app/Modules/Onboarding/Infrastructure/Persistence/Eloquent/OnboardingPlanModel.php
Create: src/backend/app/Modules/Onboarding/Infrastructure/Persistence/Eloquent/OnboardingTaskModel.php

Create: src/backend/app/Modules/Onboarding/Infrastructure/Persistence/Repositories/EloquentOnboardingTemplateRepository.php
Create: src/backend/app/Modules/Onboarding/Infrastructure/Persistence/Repositories/EloquentOnboardingPlanRepository.php
Create: src/backend/app/Modules/Onboarding/Infrastructure/Persistence/Repositories/EloquentOnboardingTaskRepository.php

Create: src/backend/app/Modules/Onboarding/Infrastructure/Http/Controllers/OnboardingTemplateController.php
Create: src/backend/app/Modules/Onboarding/Infrastructure/Http/Controllers/OnboardingPlanController.php
Create: src/backend/app/Modules/Onboarding/Infrastructure/Http/Controllers/OnboardingTaskController.php

Create: src/backend/app/Modules/Onboarding/Infrastructure/Services/PlanWorkflowService.php
Create: src/backend/app/Modules/Onboarding/Infrastructure/Services/TaskWorkflowService.php
Create: src/backend/app/Modules/Onboarding/Infrastructure/Services/NotificationService.php

Create: src/backend/app/Modules/Onboarding/Infrastructure/Listeners/CandidateHiredListener.php
Create: src/backend/app/Modules/Onboarding/Infrastructure/Jobs/PlanCompletionApprovedJob.php
Create: src/backend/app/Modules/Onboarding/Infrastructure/Jobs/TaskApprovedJob.php

Create: src/backend/app/Modules/Onboarding/Infrastructure/Seeders/OnboardingPermissionSeeder.php
Create: src/backend/app/Modules/Onboarding/Routes/api.php

Modify: src/backend/routes/api.php (add require for onboarding routes)
Modify: src/backend/app/Providers/AppServiceProvider.php (add repo bindings + module registration)
```

---

### Task 1: Schema migrations

**Files:**
- Create: `src/backend/database/migrations/2026_07_03_140001_create_onboarding_templates_table.php`
- Create: `src/backend/database/migrations/2026_07_03_140002_create_onboarding_plans_table.php`
- Create: `src/backend/database/migrations/2026_07_03_140003_create_onboarding_tasks_table.php`

- [ ] **Step 1: Create onboarding_templates migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->json('rules');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_templates');
    }
};
```

- [ ] **Step 2: Create onboarding_plans migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('employee_id')->index();
            $table->uuid('candidate_id')->nullable();
            $table->uuid('template_id')->nullable();
            $table->date('start_date');
            $table->string('status', 20)->default('draft');
            $table->uuid('workflow_request_id')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_plans');
    }
};
```

- [ ] **Step 3: Create onboarding_tasks migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('onboarding_plan_id');
            $table->string('task_type', 20);
            $table->string('owner_type', 20);
            $table->string('owner_id', 100);
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status', 20)->default('pending');
            $table->boolean('requires_approval')->default(false);
            $table->uuid('approval_workflow_request_id')->nullable();
            $table->uuid('proof_file_object_id')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_pre_start')->default(false);
            $table->timestamps();

            $table->foreign('onboarding_plan_id')->references('id')->on('onboarding_plans')->cascadeOnDelete();
            $table->index(['onboarding_plan_id', 'status']);
            $table->index(['owner_type', 'owner_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_tasks');
    }
};
```

- [ ] **Step 4: Run migrations**

```bash
docker compose run --rm app php artisan migrate
```
Expected: All tables created, no errors.

- [ ] **Step 5: Commit**

```bash
git add src/backend/database/migrations/2026_07_03_14000*.php
git commit -m "feat(onboarding): add schema migrations for templates, plans, tasks"
```

---

### Task 2: Domain Value Objects + IDs

**Files:**
- Create: `src/backend/app/Modules/Onboarding/Domain/ValueObjects/OnboardingPlanStatus.php`
- Create: `src/backend/app/Modules/Onboarding/Domain/ValueObjects/OnboardingTaskStatus.php`
- Create: `src/backend/app/Modules/Onboarding/Domain/ValueObjects/TaskType.php`
- Create: `src/backend/app/Modules/Onboarding/Domain/ValueObjects/OwnerType.php`
- Create: `src/backend/app/Modules/Onboarding/Domain/ValueObjects/TemplateRules.php`
- Create: `src/backend/app/Modules/Onboarding/Domain/Aggregates/OnboardingTemplate/OnboardingTemplateId.php`
- Create: `src/backend/app/Modules/Onboarding/Domain/Aggregates/OnboardingPlan/OnboardingPlanId.php`
- Create: `src/backend/app/Modules/Onboarding/Domain/Aggregates/OnboardingTask/OnboardingTaskId.php`

- [ ] **Step 1: Create OnboardingPlanStatus enum**

```php
<?php

namespace App\Modules\Onboarding\Domain\ValueObjects;

enum OnboardingPlanStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Draft => in_array($target, [self::Active, self::Cancelled], true),
            self::Active => in_array($target, [self::Completed, self::Cancelled], true),
            self::Completed, self::Cancelled => false,
        };
    }
}
```

- [ ] **Step 2: Create OnboardingTaskStatus enum**

```php
<?php

namespace App\Modules\Onboarding\Domain\ValueObjects;

enum OnboardingTaskStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Waived = 'waived';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Pending => in_array($target, [self::InProgress, self::Waived], true),
            self::InProgress => in_array($target, [self::Completed, self::Waived], true),
            self::Completed, self::Waived => false,
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Waived], true);
    }
}
```

- [ ] **Step 3: Create TaskType enum**

```php
<?php

namespace App\Modules\Onboarding\Domain\ValueObjects;

enum TaskType: string
{
    case SystemDefined = 'system_defined';
    case Custom = 'custom';
}
```

- [ ] **Step 4: Create OwnerType enum**

```php
<?php

namespace App\Modules\Onboarding\Domain\ValueObjects;

enum OwnerType: string
{
    case Department = 'department';
    case UserRole = 'user_role';
}
```

- [ ] **Step 5: Create TemplateRules value object**

```php
<?php

namespace App\Modules\Onboarding\Domain\ValueObjects;

use Illuminate\Support\Collection;

class TemplateRules
{
    /** @var string[] */
    private array $departments;

    /** @var string[] */
    private array $positions;

    /** @var string[] */
    private array $locations;

    /** @var string[] */
    private array $employmentTypes;

    /** @var array */
    private array $tasks;

    public function __construct(
        array $departments = [],
        array $positions = [],
        array $locations = [],
        array $employmentTypes = [],
        array $tasks = []
    ) {
        $this->departments = $departments;
        $this->positions = $positions;
        $this->locations = $locations;
        $this->employmentTypes = $employmentTypes;
        $this->tasks = $tasks;
    }

    public function matches(?string $departmentId, ?string $positionId, ?string $locationId, ?string $employmentType): bool
    {
        return (empty($this->departments) || ($departmentId && in_array($departmentId, $this->departments, true)))
            && (empty($this->positions) || ($positionId && in_array($positionId, $this->positions, true)))
            && (empty($this->locations) || ($locationId && in_array($locationId, $this->locations, true)))
            && (empty($this->employmentTypes) || ($employmentType && in_array($employmentType, $this->employmentTypes, true)));
    }

    public function getTasks(): array
    {
        return $this->tasks;
    }

    public function addTask(string $title, ?string $description, string $ownerType, string $ownerId, ?int $dueDays, bool $requiresApproval, bool $isPreStart, int $sortOrder): void
    {
        $this->tasks[] = [
            'title' => $title,
            'description' => $description,
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'due_days' => $dueDays,
            'requires_approval' => $requiresApproval,
            'is_pre_start' => $isPreStart,
            'sort_order' => $sortOrder,
        ];
    }

    public function removeTask(int $sortOrder): void
    {
        $this->tasks = array_values(
            array_filter($this->tasks, fn($t) => ($t['sort_order'] ?? 0) !== $sortOrder)
        );
    }

    public function toArray(): array
    {
        return [
            'departments' => $this->departments,
            'positions' => $this->positions,
            'locations' => $this->locations,
            'employment_types' => $this->employmentTypes,
            'tasks' => $this->tasks,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['departments'] ?? [],
            $data['positions'] ?? [],
            $data['locations'] ?? [],
            $data['employment_types'] ?? [],
            $data['tasks'] ?? [],
        );
    }
}
```

- [ ] **Step 6: Create ID value objects (OnboardingTemplateId, OnboardingPlanId, OnboardingTaskId)**

Each follows the same pattern. Example for OnboardingTemplateId:

```php
<?php

namespace App\Modules\Onboarding\Domain\Aggregates\OnboardingTemplate;

use Ramsey\Uuid\Uuid;

class OnboardingTemplateId
{
    public function __construct(private string $value) {}

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
```

Repeat the same file for `OnboardingPlanId` and `OnboardingTaskId` with matching namespace/class names.

- [ ] **Step 7: Commit**

```bash
git add src/backend/app/Modules/Onboarding/Domain/ValueObjects/*.php src/backend/app/Modules/Onboarding/Domain/Aggregates/OnboardingTemplate/OnboardingTemplateId.php src/backend/app/Modules/Onboarding/Domain/Aggregates/OnboardingPlan/OnboardingPlanId.php src/backend/app/Modules/Onboarding/Domain/Aggregates/OnboardingTask/OnboardingTaskId.php
git commit -m "feat(onboarding): add value objects and aggregate IDs"
```

---

### Task 3: Domain Exceptions

**Files:**
- Create: `src/backend/app/Modules/Onboarding/Domain/Exceptions/OnboardingPlanNotFoundException.php`
- Create: `src/backend/app/Modules/Onboarding/Domain/Exceptions/OnboardingTaskNotFoundException.php`
- Create: `src/backend/app/Modules/Onboarding/Domain/Exceptions/OnboardingTemplateNotFoundException.php`
- Create: `src/backend/app/Modules/Onboarding/Domain/Exceptions/InvalidStatusTransitionException.php`
- Create: `src/backend/app/Modules/Onboarding/Domain/Exceptions/MandatoryTaskIncompleteException.php`

- [ ] **Step 1-5: Create exception classes**

All follow the same pattern:

```php
<?php

namespace App\Modules\Onboarding\Domain\Exceptions;

class OnboardingPlanNotFoundException extends \RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("Onboarding plan not found: {$id}");
    }
}
```

Repeat for `OnboardingTaskNotFoundException`, `OnboardingTemplateNotFoundException`, `InvalidStatusTransitionException` (message: "Invalid status transition from {from} to {to}"), `MandatoryTaskIncompleteException` (message: "All mandatory tasks must be completed before completing the plan").

- [ ] **Step 6: Commit**

```bash
git add src/backend/app/Modules/Onboarding/Domain/Exceptions/*.php
git commit -m "feat(onboarding): add domain exceptions"
```

---

### Task 4: Domain Events

**Files:**
- Create: `src/backend/app/Modules/Onboarding/Domain/Events/OnboardingPlanCreated.php`
- Create: `src/backend/app/Modules/Onboarding/Domain/Events/OnboardingPlanActivated.php`
- Create: `src/backend/app/Modules/Onboarding/Domain/Events/OnboardingPlanCompleted.php`
- Create: `src/backend/app/Modules/Onboarding/Domain/Events/OnboardingTaskAssigned.php`
- Create: `src/backend/app/Modules/Onboarding/Domain/Events/OnboardingTaskStarted.php`
- Create: `src/backend/app/Modules/Onboarding/Domain/Events/OnboardingTaskCompleted.php`
- Create: `src/backend/app/Modules/Onboarding/Domain/Events/OnboardingTaskWaived.php`
- Create: `src/backend/app/Modules/Onboarding/Domain/Events/OnboardingCompleted.php`

- [ ] **Step 1-8: Create event classes**

All follow the same pattern with constructor properties:

```php
<?php

namespace App\Modules\Onboarding\Domain\Events;

use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlanId;

class OnboardingPlanCreated
{
    public function __construct(
        public readonly OnboardingPlanId $planId,
        public readonly string $employeeId,
        public readonly \DateTimeImmutable $startDate,
    ) {}
}
```

Implement all 8 events with matching properties from the spec (§5 Domain Events):
- `OnboardingPlanActivated(planId, employeeId, startDate)`
- `OnboardingPlanCompleted(planId, employeeId)`
- `OnboardingTaskAssigned(taskId, planId, ownerType, ownerId, dueDate)`
- `OnboardingTaskStarted(taskId, planId)`
- `OnboardingTaskCompleted(taskId, planId, proofFileObjectId?)`
- `OnboardingTaskWaived(taskId, planId, reason?)`
- `OnboardingCompleted(planId, employeeId)`

- [ ] **Step 9: Commit**

```bash
git add src/backend/app/Modules/Onboarding/Domain/Events/*.php
git commit -m "feat(onboarding): add domain events"
```

---

### Task 5: OnboardingTemplate Aggregate

**File:**
- Create: `src/backend/app/Modules/Onboarding/Domain/Aggregates/OnboardingTemplate/OnboardingTemplate.php`

- [ ] **Step 1: Create OnboardingTemplate aggregate**

```php
<?php

namespace App\Modules\Onboarding\Domain\Aggregates\OnboardingTemplate;

use App\Modules\Onboarding\Domain\ValueObjects\TemplateRules;

class OnboardingTemplate
{
    private function __construct(
        private readonly OnboardingTemplateId $id,
        private string $code,
        private string $name,
        private TemplateRules $rules,
        private bool $active,
    ) {}

    public static function create(
        OnboardingTemplateId $id,
        string $code,
        string $name,
        TemplateRules $rules,
    ): self {
        return new self($id, $code, $name, $rules, true);
    }

    public static function reconstitute(
        OnboardingTemplateId $id,
        string $code,
        string $name,
        TemplateRules $rules,
        bool $active,
    ): self {
        return new self($id, $code, $name, $rules, $active);
    }

    public function update(string $code, string $name, TemplateRules $rules): void
    {
        $this->code = $code;
        $this->name = $name;
        $this->rules = $rules;
    }

    public function disable(): void
    {
        $this->active = false;
    }

    public function addTemplateTask(string $title, ?string $description, string $ownerType, string $ownerId, ?int $dueDays, bool $requiresApproval, bool $isPreStart, int $sortOrder): void
    {
        $this->rules->addTask($title, $description, $ownerType, $ownerId, $dueDays, $requiresApproval, $isPreStart, $sortOrder);
    }

    public function removeTemplateTask(int $sortOrder): void
    {
        $this->rules->removeTask($sortOrder);
    }

    public function matches(?string $departmentId, ?string $positionId, ?string $locationId, ?string $employmentType): bool
    {
        return $this->active && $this->rules->matches($departmentId, $positionId, $locationId, $employmentType);
    }

    public function generatePlan(
        \App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlanId $planId,
        string $employeeId,
        ?string $candidateId,
        \DateTimeImmutable $startDate,
    ): \App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlan {
        $plan = \App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlan::create(
            $planId,
            $employeeId,
            $candidateId,
            $this->id->toString(),
            $startDate,
        );

        foreach ($this->rules->getTasks() as $taskDef) {
            $taskId = \App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTaskId::generate();
            $dueDate = $taskDef['due_days'] !== null
                ? $startDate->modify(($taskDef['due_days'] >= 0 ? '+' : '') . $taskDef['due_days'] . ' days')
                : null;

            $task = \App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTask::create(
                $taskId,
                $planId->toString(),
                \App\Modules\Onboarding\Domain\ValueObjects\TaskType::SystemDefined,
                \App\Modules\Onboarding\Domain\ValueObjects\OwnerType::from($taskDef['owner_type']),
                $taskDef['owner_id'],
                $taskDef['title'],
                $taskDef['description'] ?? null,
                $dueDate,
                $taskDef['requires_approval'] ?? false,
                $taskDef['is_pre_start'] ?? false,
                $taskDef['sort_order'] ?? 0,
            );

            $plan->addGeneratedTask($task);
        }

        return $plan;
    }

    public function getId(): OnboardingTemplateId { return $this->id; }
    public function getCode(): string { return $this->code; }
    public function getName(): string { return $this->name; }
    public function getRules(): TemplateRules { return $this->rules; }
    public function isActive(): bool { return $this->active; }
}
```

- [ ] **Step 2: Commit**

```bash
git add src/backend/app/Modules/Onboarding/Domain/Aggregates/OnboardingTemplate/OnboardingTemplate.php
git commit -m "feat(onboarding): add OnboardingTemplate aggregate"
```

---

### Task 6: OnboardingPlan Aggregate

**File:**
- Create: `src/backend/app/Modules/Onboarding/Domain/Aggregates/OnboardingPlan/OnboardingPlan.php`

- [ ] **Step 1: Create OnboardingPlan aggregate**

```php
<?php

namespace App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan;

use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTask;
use App\Modules\Onboarding\Domain\Events\OnboardingPlanActivated;
use App\Modules\Onboarding\Domain\Events\OnboardingPlanCompleted;
use App\Modules\Onboarding\Domain\Events\OnboardingPlanCreated;
use App\Modules\Onboarding\Domain\Exceptions\InvalidStatusTransitionException;
use App\Modules\Onboarding\Domain\Exceptions\MandatoryTaskIncompleteException;
use App\Modules\Onboarding\Domain\ValueObjects\OnboardingPlanStatus;
use App\Modules\Onboarding\Domain\ValueObjects\TaskType;

class OnboardingPlan
{
    /** @var OnboardingTask[] */
    private array $tasks = [];

    /** @var object[] */
    private array $recordedEvents = [];

    private function __construct(
        private readonly OnboardingPlanId $id,
        private readonly string $employeeId,
        private readonly ?string $candidateId,
        private readonly ?string $templateId,
        private readonly \DateTimeImmutable $startDate,
        private OnboardingPlanStatus $status,
        private ?string $workflowRequestId,
        private ?\DateTimeImmutable $completedAt,
    ) {}

    public static function create(
        OnboardingPlanId $id,
        string $employeeId,
        ?string $candidateId,
        ?string $templateId,
        \DateTimeImmutable $startDate,
    ): self {
        $plan = new self($id, $employeeId, $candidateId, $templateId, $startDate, OnboardingPlanStatus::Draft, null, null);
        $plan->recordEvent(new OnboardingPlanCreated($id, $employeeId, $startDate));
        return $plan;
    }

    public static function reconstitute(
        OnboardingPlanId $id,
        string $employeeId,
        ?string $candidateId,
        ?string $templateId,
        \DateTimeImmutable $startDate,
        OnboardingPlanStatus $status,
        ?string $workflowRequestId,
        ?\DateTimeImmutable $completedAt,
    ): self {
        return new self($id, $employeeId, $candidateId, $templateId, $startDate, $status, $workflowRequestId, $completedAt);
    }

    public function activate(): void
    {
        if (!$this->status->canTransitionTo(OnboardingPlanStatus::Active)) {
            throw new InvalidStatusTransitionException($this->status->value, OnboardingPlanStatus::Active->value);
        }
        if (count($this->tasks) === 0) {
            throw new \RuntimeException('Plan must have at least one task to activate');
        }
        $this->status = OnboardingPlanStatus::Active;
        $this->recordEvent(new OnboardingPlanActivated($this->id, $this->employeeId, $this->startDate));
    }

    public function cancel(): void
    {
        if (!$this->status->canTransitionTo(OnboardingPlanStatus::Cancelled)) {
            throw new InvalidStatusTransitionException($this->status->value, OnboardingPlanStatus::Cancelled->value);
        }
        $this->status = OnboardingPlanStatus::Cancelled;
    }

    public function complete(): void
    {
        if (!$this->status->canTransitionTo(OnboardingPlanStatus::Completed)) {
            throw new InvalidStatusTransitionException($this->status->value, OnboardingPlanStatus::Completed->value);
        }

        $pendingTasks = array_filter(
            $this->tasks,
            fn(OnboardingTask $t) => !$t->getStatus()->isTerminal()
        );

        if (!empty($pendingTasks)) {
            throw new MandatoryTaskIncompleteException();
        }

        // If workflow is configured, initiate approval request instead of directly completing
        // The handler sets workflowRequestId before calling this; if set, we need workflow approval
        if ($this->workflowRequestId !== null) {
            // Workflow initiated — stay active, completion comes via markWorkflowApproved callback
            return;
        }

        $this->status = OnboardingPlanStatus::Completed;
        $this->completedAt = new \DateTimeImmutable();
        $this->recordEvent(new OnboardingPlanCompleted($this->id, $this->employeeId));
    }

    public function markWorkflowApproved(): void
    {
        if ($this->status !== OnboardingPlanStatus::Active) {
            throw new InvalidStatusTransitionException($this->status->value, OnboardingPlanStatus::Completed->value);
        }
        $this->status = OnboardingPlanStatus::Completed;
        $this->completedAt = new \DateTimeImmutable();
        $this->recordEvent(new OnboardingPlanCompleted($this->id, $this->employeeId));
    }

    public function addTask(OnboardingTask $task): void
    {
        if ($this->status !== OnboardingPlanStatus::Active) {
            throw new \RuntimeException('Can only add tasks to active plans');
        }
        if ($task->getTaskType() !== TaskType::Custom) {
            throw new \RuntimeException('Only custom tasks can be added manually');
        }
        if (count($this->tasks) >= 50) {
            throw new \RuntimeException('Maximum 50 custom tasks per plan');
        }
        $this->tasks[] = $task;
        $this->recordEvent(new \App\Modules\Onboarding\Domain\Events\OnboardingTaskAssigned(
            $task->getId(), $this->id, $task->getOwnerType()->value, $task->getOwnerId(), $task->getDueDate()
        ));
    }

    public function addGeneratedTask(OnboardingTask $task): void
    {
        $this->tasks[] = $task;
    }

    public function removeTask(string $taskId): void
    {
        foreach ($this->tasks as $i => $task) {
            if ($task->getId()->toString() === $taskId) {
                if ($task->getTaskType() !== TaskType::Custom) {
                    throw new \RuntimeException('Cannot remove system-defined tasks');
                }
                unset($this->tasks[$i]);
                $this->tasks = array_values($this->tasks);
                return;
            }
        }
        throw new \RuntimeException("Task not found: {$taskId}");
    }

    public function setWorkflowRequestId(string $workflowRequestId): void
    {
        $this->workflowRequestId = $workflowRequestId;
    }

    public function recordEvent(object $event): void
    {
        $this->recordedEvents[] = $event;
    }

    public function popRecordedEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];
        return $events;
    }

    // Getters
    public function getId(): OnboardingPlanId { return $this->id; }
    public function getEmployeeId(): string { return $this->employeeId; }
    public function getCandidateId(): ?string { return $this->candidateId; }
    public function getTemplateId(): ?string { return $this->templateId; }
    public function getStartDate(): \DateTimeImmutable { return $this->startDate; }
    public function getStatus(): OnboardingPlanStatus { return $this->status; }
    public function getWorkflowRequestId(): ?string { return $this->workflowRequestId; }
    public function getCompletedAt(): ?\DateTimeImmutable { return $this->completedAt; }
    public function getTasks(): array { return $this->tasks; }
}
```

- [ ] **Step 2: Commit**

```bash
git add src/backend/app/Modules/Onboarding/Domain/Aggregates/OnboardingPlan/OnboardingPlan.php
git commit -m "feat(onboarding): add OnboardingPlan aggregate"
```

---

### Task 7: OnboardingTask Aggregate

**File:**
- Create: `src/backend/app/Modules/Onboarding/Domain/Aggregates/OnboardingTask/OnboardingTask.php`

- [ ] **Step 1: Create OnboardingTask aggregate**

```php
<?php

namespace App\Modules\Onboarding\Domain\Aggregates\OnboardingTask;

use App\Modules\Onboarding\Domain\Events\OnboardingTaskCompleted;
use App\Modules\Onboarding\Domain\Events\OnboardingTaskStarted;
use App\Modules\Onboarding\Domain\Events\OnboardingTaskWaived;
use App\Modules\Onboarding\Domain\Exceptions\InvalidStatusTransitionException;
use App\Modules\Onboarding\Domain\ValueObjects\OnboardingTaskStatus;
use App\Modules\Onboarding\Domain\ValueObjects\OwnerType;
use App\Modules\Onboarding\Domain\ValueObjects\TaskType;

class OnboardingTask
{
    /** @var object[] */
    private array $recordedEvents = [];

    private function __construct(
        private readonly OnboardingTaskId $id,
        private readonly string $planId,
        private readonly TaskType $taskType,
        private readonly OwnerType $ownerType,
        private readonly string $ownerId,
        private string $title,
        private ?string $description,
        private ?\DateTimeImmutable $dueDate,
        private OnboardingTaskStatus $status,
        private bool $requiresApproval,
        private ?string $approvalWorkflowRequestId,
        private ?string $proofFileObjectId,
        private bool $isPreStart,
        private int $sortOrder,
    ) {}

    public static function create(
        OnboardingTaskId $id,
        string $planId,
        TaskType $taskType,
        OwnerType $ownerType,
        string $ownerId,
        string $title,
        ?string $description,
        ?\DateTimeImmutable $dueDate,
        bool $requiresApproval,
        bool $isPreStart,
        int $sortOrder,
    ): self {
        return new self(
            $id, $planId, $taskType, $ownerType, $ownerId, $title, $description,
            $dueDate, OnboardingTaskStatus::Pending, $requiresApproval, null, null, $isPreStart, $sortOrder
        );
    }

    public static function reconstitute(
        OnboardingTaskId $id,
        string $planId,
        TaskType $taskType,
        OwnerType $ownerType,
        string $ownerId,
        string $title,
        ?string $description,
        ?\DateTimeImmutable $dueDate,
        OnboardingTaskStatus $status,
        bool $requiresApproval,
        ?string $approvalWorkflowRequestId,
        ?string $proofFileObjectId,
        bool $isPreStart,
        int $sortOrder,
    ): self {
        return new self(
            $id, $planId, $taskType, $ownerType, $ownerId, $title, $description,
            $dueDate, $status, $requiresApproval, $approvalWorkflowRequestId,
            $proofFileObjectId, $isPreStart, $sortOrder
        );
    }

    public function update(string $title, ?string $description): void
    {
        if ($this->status->isTerminal()) {
            throw new \RuntimeException('Cannot update a completed or waived task');
        }
        $this->title = $title;
        $this->description = $description;
    }

    public function start(): void
    {
        if (!$this->status->canTransitionTo(OnboardingTaskStatus::InProgress)) {
            throw new InvalidStatusTransitionException($this->status->value, OnboardingTaskStatus::InProgress->value);
        }
        $this->status = OnboardingTaskStatus::InProgress;
        $this->recordEvent(new OnboardingTaskStarted($this->id, $this->planId));
    }

    public function complete(?string $proofFileObjectId = null): void
    {
        if (!$this->status->canTransitionTo(OnboardingTaskStatus::Completed)) {
            throw new InvalidStatusTransitionException($this->status->value, OnboardingTaskStatus::Completed->value);
        }

        if ($this->requiresApproval) {
            // Task completion requires workflow approval — handler will initiate workflow
            // Store proof file ref, but status stays in_progress until markApproved() is called
            $this->proofFileObjectId = $proofFileObjectId;
            return;
        }

        $this->proofFileObjectId = $proofFileObjectId;
        $this->status = OnboardingTaskStatus::Completed;
        $this->recordEvent(new OnboardingTaskCompleted($this->id, $this->planId, $proofFileObjectId));
    }

    public function waive(?string $reason = null): void
    {
        if (!$this->status->canTransitionTo(OnboardingTaskStatus::Waived)) {
            throw new InvalidStatusTransitionException($this->status->value, OnboardingTaskStatus::Waived->value);
        }
        $this->status = OnboardingTaskStatus::Waived;
        $this->recordEvent(new OnboardingTaskWaived($this->id, $this->planId, $reason));
    }

    public function markApproved(): void
    {
        if ($this->status !== OnboardingTaskStatus::InProgress) {
            throw new InvalidStatusTransitionException($this->status->value, OnboardingTaskStatus::Completed->value);
        }
        if (!$this->requiresApproval) {
            throw new \RuntimeException('Task does not require approval');
        }
        $this->status = OnboardingTaskStatus::Completed;
        $this->recordEvent(new OnboardingTaskCompleted($this->id, $this->planId, $this->proofFileObjectId));
    }

    public function setApprovalWorkflowRequestId(string $id): void { $this->approvalWorkflowRequestId = $id; }

    public function recordEvent(object $event): void { $this->recordedEvents[] = $event; }
    public function popRecordedEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];
        return $events;
    }

    // Getters
    public function getId(): OnboardingTaskId { return $this->id; }
    public function getPlanId(): string { return $this->planId; }
    public function getTaskType(): TaskType { return $this->taskType; }
    public function getOwnerType(): OwnerType { return $this->ownerType; }
    public function getOwnerId(): string { return $this->ownerId; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function getDueDate(): ?\DateTimeImmutable { return $this->dueDate; }
    public function getStatus(): OnboardingTaskStatus { return $this->status; }
    public function isRequiresApproval(): bool { return $this->requiresApproval; }
    public function getApprovalWorkflowRequestId(): ?string { return $this->approvalWorkflowRequestId; }
    public function getProofFileObjectId(): ?string { return $this->proofFileObjectId; }
    public function getSortOrder(): int { return $this->sortOrder; }
    public function isPreStart(): bool { return $this->isPreStart; }
}
```

- [ ] **Step 2: Commit**

```bash
git add src/backend/app/Modules/Onboarding/Domain/Aggregates/OnboardingTask/OnboardingTask.php
git commit -m "feat(onboarding): add OnboardingTask aggregate"
```

---

### Task 8: Repository Interfaces + Eloquent Models

**Files:**
- Create: `src/backend/app/Modules/Onboarding/Domain/Repositories/OnboardingTemplateRepositoryInterface.php`
- Create: `src/backend/app/Modules/Onboarding/Domain/Repositories/OnboardingPlanRepositoryInterface.php`
- Create: `src/backend/app/Modules/Onboarding/Domain/Repositories/OnboardingTaskRepositoryInterface.php`
- Create: `src/backend/app/Modules/Onboarding/Infrastructure/Persistence/Eloquent/OnboardingTemplateModel.php`
- Create: `src/backend/app/Modules/Onboarding/Infrastructure/Persistence/Eloquent/OnboardingPlanModel.php`
- Create: `src/backend/app/Modules/Onboarding/Infrastructure/Persistence/Eloquent/OnboardingTaskModel.php`

- [ ] **Step 1: OnboardingTemplateRepositoryInterface**

```php
<?php

namespace App\Modules\Onboarding\Domain\Repositories;

use App\Modules\Onboarding\Domain\Aggregates\OnboardingTemplate\OnboardingTemplate;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTemplate\OnboardingTemplateId;

interface OnboardingTemplateRepositoryInterface
{
    public function findById(OnboardingTemplateId $id): ?OnboardingTemplate;
    public function findByCode(string $code): ?OnboardingTemplate;
    /** @return OnboardingTemplate[] */
    public function findMatching(?string $departmentId, ?string $positionId, ?string $locationId, ?string $employmentType): array;
    /** @return OnboardingTemplate[] */
    public function all(): array;
    public function save(OnboardingTemplate $template): void;
    public function delete(OnboardingTemplateId $id): void;
}
```

- [ ] **Step 2: OnboardingPlanRepositoryInterface**

```php
<?php

namespace App\Modules\Onboarding\Domain\Repositories;

use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlan;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlanId;

interface OnboardingPlanRepositoryInterface
{
    public function findById(OnboardingPlanId $id): ?OnboardingPlan;
    /** @return OnboardingPlan[] */
    public function findByEmployeeId(string $employeeId): array;
    public function findByWorkflowRequestId(string $workflowRequestId): ?OnboardingPlan;
    /** @return OnboardingPlan[] */
    public function all(): array;
    public function save(OnboardingPlan $plan): void;
    public function delete(OnboardingPlanId $id): void;
}
```

- [ ] **Step 3: OnboardingTaskRepositoryInterface**

```php
<?php

namespace App\Modules\Onboarding\Domain\Repositories;

use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTask;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTaskId;

interface OnboardingTaskRepositoryInterface
{
    public function findById(OnboardingTaskId $id): ?OnboardingTask;
    /** @return OnboardingTask[] */
    public function findByPlanId(string $planId): array;
    /** @return OnboardingTask[] */
    public function findByOwner(string $ownerType, string $ownerId): array;
    public function findByApprovalWorkflowRequestId(string $requestId): ?OnboardingTask;
    public function save(OnboardingTask $task): void;
    public function delete(OnboardingTaskId $id): void;
}
```

- [ ] **Step 4: OnboardingTemplateModel (Eloquent)**

```php
<?php

namespace App\Modules\Onboarding\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class OnboardingTemplateModel extends Model
{
    use HasUuids;

    protected $table = 'onboarding_templates';

    protected $fillable = [
        'id', 'code', 'name', 'rules', 'active',
    ];

    protected function casts(): array
    {
        return [
            'rules' => 'array',
            'active' => 'boolean',
        ];
    }
}
```

- [ ] **Step 5: OnboardingPlanModel**

```php
<?php

namespace App\Modules\Onboarding\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class OnboardingPlanModel extends Model
{
    use HasUuids;

    protected $table = 'onboarding_plans';

    protected $fillable = [
        'id', 'employee_id', 'candidate_id', 'template_id',
        'start_date', 'status', 'workflow_request_id', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function tasks()
    {
        return $this->hasMany(OnboardingTaskModel::class, 'onboarding_plan_id');
    }
}
```

- [ ] **Step 6: OnboardingTaskModel**

```php
<?php

namespace App\Modules\Onboarding\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class OnboardingTaskModel extends Model
{
    use HasUuids;

    protected $table = 'onboarding_tasks';

    protected $fillable = [
        'id', 'onboarding_plan_id', 'task_type', 'owner_type', 'owner_id',
        'title', 'description', 'due_date', 'status', 'requires_approval',
        'approval_workflow_request_id', 'proof_file_object_id', 'sort_order', 'is_pre_start',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'requires_approval' => 'boolean',
            'is_pre_start' => 'boolean',
        ];
    }
}
```

- [ ] **Step 7: Commit**

```bash
git add src/backend/app/Modules/Onboarding/Domain/Repositories/*.php src/backend/app/Modules/Onboarding/Infrastructure/Persistence/Eloquent/*.php
git commit -m "feat(onboarding): add repository interfaces and Eloquent models"
```

---

### Task 9: Eloquent Repository Implementations + Service Provider Bindings

**Files:**
- Create: `src/backend/app/Modules/Onboarding/Infrastructure/Persistence/Repositories/EloquentOnboardingTemplateRepository.php`
- Create: `src/backend/app/Modules/Onboarding/Infrastructure/Persistence/Repositories/EloquentOnboardingPlanRepository.php`
- Create: `src/backend/app/Modules/Onboarding/Infrastructure/Persistence/Repositories/EloquentOnboardingTaskRepository.php`
- Modify: `src/backend/app/Providers/AppServiceProvider.php`

- [ ] **Step 1: EloquentOnboardingTemplateRepository**

```php
<?php

namespace App\Modules\Onboarding\Infrastructure\Persistence\Repositories;

use App\Modules\Onboarding\Domain\Aggregates\OnboardingTemplate\OnboardingTemplate;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTemplate\OnboardingTemplateId;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTemplateRepositoryInterface;
use App\Modules\Onboarding\Domain\ValueObjects\TemplateRules;
use App\Modules\Onboarding\Infrastructure\Persistence\Eloquent\OnboardingTemplateModel;

class EloquentOnboardingTemplateRepository implements OnboardingTemplateRepositoryInterface
{
    public function findById(OnboardingTemplateId $id): ?OnboardingTemplate
    {
        $model = OnboardingTemplateModel::find($id->toString());
        return $model ? $this->toDomain($model) : null;
    }

    public function findByCode(string $code): ?OnboardingTemplate
    {
        $model = OnboardingTemplateModel::where('code', $code)->first();
        return $model ? $this->toDomain($model) : null;
    }

    public function findMatching(?string $departmentId, ?string $positionId, ?string $locationId, ?string $employmentType): array
    {
        $models = OnboardingTemplateModel::where('active', true)->get();
        return array_values(
            array_filter(
                $models->map(fn($m) => $this->toDomain($m))->toArray(),
                fn(OnboardingTemplate $t) => $t->matches($departmentId, $positionId, $locationId, $employmentType)
            )
        );
    }

    public function all(): array
    {
        return OnboardingTemplateModel::all()->map(fn($m) => $this->toDomain($m))->toArray();
    }

    public function save(OnboardingTemplate $template): void
    {
        OnboardingTemplateModel::updateOrCreate(
            ['id' => $template->getId()->toString()],
            [
                'code' => $template->getCode(),
                'name' => $template->getName(),
                'rules' => $template->getRules()->toArray(),
                'active' => $template->isActive(),
            ]
        );
    }

    public function delete(OnboardingTemplateId $id): void
    {
        OnboardingTemplateModel::destroy($id->toString());
    }

    private function toDomain(OnboardingTemplateModel $model): OnboardingTemplate
    {
        return OnboardingTemplate::reconstitute(
            OnboardingTemplateId::fromString($model->id),
            $model->code,
            $model->name,
            TemplateRules::fromArray($model->rules),
            $model->active,
        );
    }
}
```

- [ ] **Step 2: EloquentOnboardingPlanRepository**

```php
<?php

namespace App\Modules\Onboarding\Infrastructure\Persistence\Repositories;

use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlan;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlanId;
use App\Modules\Onboarding\Domain\Repositories\OnboardingPlanRepositoryInterface;
use App\Modules\Onboarding\Domain\ValueObjects\OnboardingPlanStatus;
use App\Modules\Onboarding\Infrastructure\Persistence\Eloquent\OnboardingPlanModel;
use App\Modules\Onboarding\Infrastructure\Persistence\Eloquent\OnboardingTaskModel;

class EloquentOnboardingPlanRepository implements OnboardingPlanRepositoryInterface
{
    public function findById(OnboardingPlanId $id): ?OnboardingPlan
    {
        $model = OnboardingPlanModel::with('tasks')->find($id->toString());
        return $model ? $this->toDomain($model) : null;
    }

    public function findByEmployeeId(string $employeeId): array
    {
        return OnboardingPlanModel::with('tasks')
            ->where('employee_id', $employeeId)
            ->get()
            ->map(fn($m) => $this->toDomain($m))
            ->toArray();
    }

    public function all(): array
    {
        return OnboardingPlanModel::with('tasks')
            ->get()
            ->map(fn($m) => $this->toDomain($m))
            ->toArray();
    }

    public function save(OnboardingPlan $plan): void
    {
        OnboardingPlanModel::updateOrCreate(
            ['id' => $plan->getId()->toString()],
            [
                'employee_id' => $plan->getEmployeeId(),
                'candidate_id' => $plan->getCandidateId(),
                'template_id' => $plan->getTemplateId(),
                'start_date' => $plan->getStartDate()->format('Y-m-d'),
                'status' => $plan->getStatus()->value,
                'workflow_request_id' => $plan->getWorkflowRequestId(),
                'completed_at' => $plan->getCompletedAt()?->format('Y-m-d H:i:s'),
            ]
        );

        foreach ($plan->getTasks() as $task) {
            $taskRepo = app(OnboardingTaskRepositoryInterface::class);
            $taskRepo->save($task);
        }
    }

    public function delete(OnboardingPlanId $id): void
    {
        OnboardingPlanModel::destroy($id->toString());
    }

    private function toDomain(OnboardingPlanModel $model): OnboardingPlan
    {
        $plan = OnboardingPlan::reconstitute(
            OnboardingPlanId::fromString($model->id),
            $model->employee_id,
            $model->candidate_id,
            $model->template_id,
            new \DateTimeImmutable($model->start_date),
            OnboardingPlanStatus::from($model->status),
            $model->workflow_request_id,
            $model->completed_at ? new \DateTimeImmutable($model->completed_at) : null,
        );

        foreach ($model->tasks ?? [] as $taskModel) {
            $task = $this->taskToDomain($taskModel);
            // Use reflection or a package method to add tasks to reconstituted plan
            $plan->addGeneratedTask($task);
        }

        return $plan;
    }

    private function taskToDomain(OnboardingTaskModel $model): \App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTask
    {
        return \App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTask::reconstitute(
            \App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTaskId::fromString($model->id),
            $model->onboarding_plan_id,
            \App\Modules\Onboarding\Domain\ValueObjects\TaskType::from($model->task_type),
            \App\Modules\Onboarding\Domain\ValueObjects\OwnerType::from($model->owner_type),
            $model->owner_id,
            $model->title,
            $model->description,
            $model->due_date ? new \DateTimeImmutable($model->due_date) : null,
            \App\Modules\Onboarding\Domain\ValueObjects\OnboardingTaskStatus::from($model->status),
            $model->requires_approval,
            $model->approval_workflow_request_id,
            $model->proof_file_object_id,
            $model->is_pre_start,
            $model->sort_order,
        );
    }
}
```

- [ ] **Step 3: EloquentOnboardingTaskRepository**

```php
<?php

namespace App\Modules\Onboarding\Infrastructure\Persistence\Repositories;

use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTask;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTaskId;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTaskRepositoryInterface;
use App\Modules\Onboarding\Domain\ValueObjects\OnboardingTaskStatus;
use App\Modules\Onboarding\Domain\ValueObjects\OwnerType;
use App\Modules\Onboarding\Domain\ValueObjects\TaskType;
use App\Modules\Onboarding\Infrastructure\Persistence\Eloquent\OnboardingTaskModel;

class EloquentOnboardingTaskRepository implements OnboardingTaskRepositoryInterface
{
    public function findById(OnboardingTaskId $id): ?OnboardingTask
    {
        $model = OnboardingTaskModel::find($id->toString());
        return $model ? $this->toDomain($model) : null;
    }

    public function findByPlanId(string $planId): array
    {
        return OnboardingTaskModel::where('onboarding_plan_id', $planId)
            ->orderBy('sort_order')
            ->get()
            ->map(fn($m) => $this->toDomain($m))
            ->toArray();
    }

    public function findByOwner(string $ownerType, string $ownerId): array
    {
        return OnboardingTaskModel::where('owner_type', $ownerType)
            ->where('owner_id', $ownerId)
            ->get()
            ->map(fn($m) => $this->toDomain($m))
            ->toArray();
    }

    public function save(OnboardingTask $task): void
    {
        OnboardingTaskModel::updateOrCreate(
            ['id' => $task->getId()->toString()],
            [
                'onboarding_plan_id' => $task->getPlanId(),
                'task_type' => $task->getTaskType()->value,
                'owner_type' => $task->getOwnerType()->value,
                'owner_id' => $task->getOwnerId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'due_date' => $task->getDueDate()?->format('Y-m-d'),
                'status' => $task->getStatus()->value,
                'requires_approval' => $task->isRequiresApproval(),
                'approval_workflow_request_id' => $task->getApprovalWorkflowRequestId(),
                'proof_file_object_id' => $task->getProofFileObjectId(),
                'sort_order' => $task->getSortOrder(),
                'is_pre_start' => $task->isPreStart(),
            ]
        );
    }

    public function delete(OnboardingTaskId $id): void
    {
        OnboardingTaskModel::destroy($id->toString());
    }

    private function toDomain(OnboardingTaskModel $model): OnboardingTask
    {
        return OnboardingTask::reconstitute(
            OnboardingTaskId::fromString($model->id),
            $model->onboarding_plan_id,
            TaskType::from($model->task_type),
            OwnerType::from($model->owner_type),
            $model->owner_id,
            $model->title,
            $model->description,
            $model->due_date ? new \DateTimeImmutable($model->due_date) : null,
            OnboardingTaskStatus::from($model->status),
            $model->requires_approval,
            $model->approval_workflow_request_id,
            $model->proof_file_object_id,
            $model->is_pre_start,
            $model->sort_order,
        );
    }
}
```

- [ ] **Step 4: Modify AppServiceProvider**

Add to `src/backend/app/Providers/AppServiceProvider.php` `register()` method:

```php
// Onboarding module
$this->app->bind(
    \App\Modules\Onboarding\Domain\Repositories\OnboardingTemplateRepositoryInterface::class,
    \App\Modules\Onboarding\Infrastructure\Persistence\Repositories\EloquentOnboardingTemplateRepository::class,
);
$this->app->bind(
    \App\Modules\Onboarding\Domain\Repositories\OnboardingPlanRepositoryInterface::class,
    \App\Modules\Onboarding\Infrastructure\Persistence\Repositories\EloquentOnboardingPlanRepository::class,
);
$this->app->bind(
    \App\Modules\Onboarding\Domain\Repositories\OnboardingTaskRepositoryInterface::class,
    \App\Modules\Onboarding\Infrastructure\Persistence\Repositories\EloquentOnboardingTaskRepository::class,
);
```

- [ ] **Step 5: Commit**

```bash
git add src/backend/app/Modules/Onboarding/Infrastructure/Persistence/Repositories/*.php src/backend/app/Providers/AppServiceProvider.php
git commit -m "feat(onboarding): add Eloquent repository implementations and service provider bindings"
```

---

### Task 10: Application Commands + Handlers (Template + Plan creation)

**Files:**
- Create: `src/backend/app/Modules/Onboarding/Application/Commands/CreateOnboardingTemplateCommand.php`
- Create: `src/backend/app/Modules/Onboarding/Application/Commands/UpdateOnboardingTemplateCommand.php`
- Create: `src/backend/app/Modules/Onboarding/Application/Commands/CreateOnboardingPlanCommand.php`
- Create: `src/backend/app/Modules/Onboarding/Application/CommandHandlers/CreateOnboardingTemplateHandler.php`
- Create: `src/backend/app/Modules/Onboarding/Application/CommandHandlers/UpdateOnboardingTemplateHandler.php`
- Create: `src/backend/app/Modules/Onboarding/Application/CommandHandlers/CreateOnboardingPlanHandler.php`

- [ ] **Step 1: CreateOnboardingTemplateCommand**

```php
<?php

namespace App\Modules\Onboarding\Application\Commands;

class CreateOnboardingTemplateCommand
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly array $rules,
    ) {}
}
```

- [ ] **Step 2: UpdateOnboardingTemplateCommand**

```php
<?php

namespace App\Modules\Onboarding\Application\Commands;

class UpdateOnboardingTemplateCommand
{
    public function __construct(
        public readonly string $id,
        public readonly string $code,
        public readonly string $name,
        public readonly array $rules,
    ) {}
}
```

- [ ] **Step 3: CreateOnboardingPlanCommand**

```php
<?php

namespace App\Modules\Onboarding\Application\Commands;

class CreateOnboardingPlanCommand
{
    public function __construct(
        public readonly string $employeeId,
        public readonly ?string $candidateId,
        public readonly ?string $templateId,
        public readonly string $startDate, // Y-m-d
    ) {}
}
```

- [ ] **Step 4: CreateOnboardingTemplateHandler**

```php
<?php

namespace App\Modules\Onboarding\Application\CommandHandlers;

use App\Modules\Onboarding\Application\Commands\CreateOnboardingTemplateCommand;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTemplate\OnboardingTemplate;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTemplate\OnboardingTemplateId;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTemplateRepositoryInterface;
use App\Modules\Onboarding\Domain\ValueObjects\TemplateRules;

class CreateOnboardingTemplateHandler
{
    public function __construct(
        private readonly OnboardingTemplateRepositoryInterface $templateRepo,
    ) {}

    public function handle(CreateOnboardingTemplateCommand $command): OnboardingTemplate
    {
        $rules = TemplateRules::fromArray($command->rules);
        $template = OnboardingTemplate::create(
            OnboardingTemplateId::generate(),
            $command->code,
            $command->name,
            $rules,
        );

        $this->templateRepo->save($template);

        return $template;
    }
}
```

- [ ] **Step 5: UpdateOnboardingTemplateHandler**

```php
<?php

namespace App\Modules\Onboarding\Application\CommandHandlers;

use App\Modules\Onboarding\Application\Commands\UpdateOnboardingTemplateCommand;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTemplate\OnboardingTemplateId;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTemplateRepositoryInterface;
use App\Modules\Onboarding\Domain\ValueObjects\TemplateRules;
use App\Modules\Onboarding\Domain\Exceptions\OnboardingTemplateNotFoundException;

class UpdateOnboardingTemplateHandler
{
    public function __construct(
        private readonly OnboardingTemplateRepositoryInterface $templateRepo,
    ) {}

    public function handle(UpdateOnboardingTemplateCommand $command): OnboardingTemplate
    {
        $id = OnboardingTemplateId::fromString($command->id);
        $template = $this->templateRepo->findById($id);
        if (!$template) {
            throw new OnboardingTemplateNotFoundException($command->id);
        }

        $template->update($command->code, $command->name, TemplateRules::fromArray($command->rules));
        $this->templateRepo->save($template);

        return $template;
    }
}
```

- [ ] **Step 6: CreateOnboardingPlanHandler**

```php
<?php

namespace App\Modules\Onboarding\Application\CommandHandlers;

use App\Modules\Onboarding\Application\Commands\CreateOnboardingPlanCommand;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlan;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlanId;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTemplate\OnboardingTemplateId;
use App\Modules\Onboarding\Domain\Repositories\OnboardingPlanRepositoryInterface;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTemplateRepositoryInterface;

class CreateOnboardingPlanHandler
{
    public function __construct(
        private readonly OnboardingPlanRepositoryInterface $planRepo,
        private readonly OnboardingTemplateRepositoryInterface $templateRepo,
    ) {}

    public function handle(CreateOnboardingPlanCommand $command): OnboardingPlan
    {
        $planId = OnboardingPlanId::generate();

        if ($command->templateId) {
            $templateId = OnboardingTemplateId::fromString($command->templateId);
            $template = $this->templateRepo->findById($templateId);
            if (!$template) {
                throw new \App\Modules\Onboarding\Domain\Exceptions\OnboardingTemplateNotFoundException($command->templateId);
            }

            $plan = $template->generatePlan(
                $planId,
                $command->employeeId,
                $command->candidateId,
                new \DateTimeImmutable($command->startDate),
            );
        } else {
            $plan = OnboardingPlan::create(
                $planId,
                $command->employeeId,
                $command->candidateId,
                null,
                new \DateTimeImmutable($command->startDate),
            );
        }

        $this->planRepo->save($plan);
        return $plan;
    }
}
```

- [ ] **Step 7: Commit**

```bash
git add src/backend/app/Modules/Onboarding/Application/Commands/CreateOnboarding*.php src/backend/app/Modules/Onboarding/Application/Commands/UpdateOnboarding*.php src/backend/app/Modules/Onboarding/Application/CommandHandlers/CreateOnboarding*.php src/backend/app/Modules/Onboarding/Application/CommandHandlers/UpdateOnboarding*.php
git commit -m "feat(onboarding): add template and plan creation command handlers"
```

---

### Task 11: Application Handlers (Plan lifecycle — activate, cancel, complete)

**Files:**
- Create: `src/backend/app/Modules/Onboarding/Application/Commands/ActivateOnboardingPlanCommand.php`
- Create: `src/backend/app/Modules/Onboarding/Application/Commands/CancelOnboardingPlanCommand.php`
- Create: `src/backend/app/Modules/Onboarding/Application/Commands/CompleteOnboardingPlanCommand.php`
- Create: `src/backend/app/Modules/Onboarding/Application/CommandHandlers/ActivateOnboardingPlanHandler.php`
- Create: `src/backend/app/Modules/Onboarding/Application/CommandHandlers/CancelOnboardingPlanHandler.php`
- Create: `src/backend/app/Modules/Onboarding/Application/CommandHandlers/CompleteOnboardingPlanHandler.php`

- [ ] **Step 1: ActivateOnboardingPlanHandler**

```php
<?php

namespace App\Modules\Onboarding\Application\Commands;

class ActivateOnboardingPlanCommand
{
    public function __construct(public readonly string $planId) {}
}
```

```php
<?php

namespace App\Modules\Onboarding\Application\CommandHandlers;

use App\Modules\Onboarding\Application\Commands\ActivateOnboardingPlanCommand;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlanId;
use App\Modules\Onboarding\Domain\Repositories\OnboardingPlanRepositoryInterface;
use App\Modules\Onboarding\Domain\Exceptions\OnboardingPlanNotFoundException;

class ActivateOnboardingPlanHandler
{
    public function __construct(
        private readonly OnboardingPlanRepositoryInterface $planRepo,
    ) {}

    public function handle(ActivateOnboardingPlanCommand $command): void
    {
        $planId = OnboardingPlanId::fromString($command->planId);
        $plan = $this->planRepo->findById($planId);
        if (!$plan) {
            throw new OnboardingPlanNotFoundException($command->planId);
        }

        $plan->activate();
        $this->planRepo->save($plan);
        // Dispatch domain events
        foreach ($plan->popRecordedEvents() as $event) {
            event($event);
        }
    }
}
```

- [ ] **Step 2: CancelOnboardingPlanHandler**


```php
<?php

namespace App\Modules\Onboarding\Application\Commands;

class CancelOnboardingPlanCommand
{
    public function __construct(public readonly string $planId) {}
}
```

```php
<?php

namespace App\Modules\Onboarding\Application\CommandHandlers;

use App\Modules\Onboarding\Application\Commands\CancelOnboardingPlanCommand;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlanId;
use App\Modules\Onboarding\Domain\Repositories\OnboardingPlanRepositoryInterface;
use App\Modules\Onboarding\Domain\Exceptions\OnboardingPlanNotFoundException;

class CancelOnboardingPlanHandler
{
    public function __construct(
        private readonly OnboardingPlanRepositoryInterface $planRepo,
    ) {}

    public function handle(CancelOnboardingPlanCommand $command): void
    {
        $planId = OnboardingPlanId::fromString($command->planId);
        $plan = $this->planRepo->findById($planId);
        if (!$plan) {
            throw new OnboardingPlanNotFoundException($command->planId);
        }

        $plan->cancel();
        $this->planRepo->save($plan);

        foreach ($plan->popRecordedEvents() as $event) {
            event($event);
        }
    }
}
```


- [ ] **Step 3: CompleteOnboardingPlanCommand & Handler**

```php
<?php

namespace App\Modules\Onboarding\Application\Commands;

class CompleteOnboardingPlanCommand
{
    public function __construct(
        public readonly string $planId,
        public readonly ?string $workflowTemplateId = null,
    ) {}
}
```

Handler:

```php
<?php

namespace App\Modules\Onboarding\Application\CommandHandlers;

use App\Modules\Onboarding\Application\Commands\CompleteOnboardingPlanCommand;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlanId;
use App\Modules\Onboarding\Domain\Repositories\OnboardingPlanRepositoryInterface;
use App\Modules\Onboarding\Domain\Exceptions\OnboardingPlanNotFoundException;
use App\Modules\Onboarding\Infrastructure\Services\PlanWorkflowService;

class CompleteOnboardingPlanHandler
{
    public function __construct(
        private readonly OnboardingPlanRepositoryInterface $planRepo,
        private readonly PlanWorkflowService $workflowService,
    ) {}

    public function handle(CompleteOnboardingPlanCommand $command): void
    {
        $planId = OnboardingPlanId::fromString($command->planId);
        $plan = $this->planRepo->findById($planId);
        if (!$plan) {
            throw new OnboardingPlanNotFoundException($command->planId);
        }

        if ($command->workflowTemplateId) {
            $requestId = $this->workflowService->startWorkflow(
                $command->workflowTemplateId,
                'onboarding_plan',
                $command->planId,
            );
            $plan->setWorkflowRequestId($requestId);
            $plan->complete(); // validates tasks, sees workflowRequestId is set, stays active
        } else {
            $plan->complete(); // directly completes if no workflow
        }

        $this->planRepo->save($plan);
        foreach ($plan->popRecordedEvents() as $event) {
            event($event);
        }
    }
}
```

- [ ] **Step 4: Commit**

```bash
git add src/backend/app/Modules/Onboarding/Application/Commands/Activate*.php src/backend/app/Modules/Onboarding/Application/Commands/Cancel*.php src/backend/app/Modules/Onboarding/Application/Commands/Complete*.php src/backend/app/Modules/Onboarding/Application/CommandHandlers/Activate*.php src/backend/app/Modules/Onboarding/Application/CommandHandlers/Cancel*.php src/backend/app/Modules/Onboarding/Application/CommandHandlers/Complete*.php
git commit -m "feat(onboarding): add plan lifecycle command handlers (activate, cancel, complete)"
```

---

### Task 12: Application Handlers (Task lifecycle — add, remove, start, complete, waive)

**Files:**
- Create: `src/backend/app/Modules/Onboarding/Application/Commands/AddOnboardingTaskCommand.php`
- Create: `src/backend/app/Modules/Onboarding/Application/Commands/RemoveOnboardingTaskCommand.php`
- Create: `src/backend/app/Modules/Onboarding/Application/Commands/StartTaskCommand.php`
- Create: `src/backend/app/Modules/Onboarding/Application/Commands/CompleteTaskCommand.php`
- Create: `src/backend/app/Modules/Onboarding/Application/Commands/WaiveTaskCommand.php`
- Create: `src/backend/app/Modules/Onboarding/Application/CommandHandlers/AddOnboardingTaskHandler.php`
- Create: `src/backend/app/Modules/Onboarding/Application/CommandHandlers/RemoveOnboardingTaskHandler.php`
- Create: `src/backend/app/Modules/Onboarding/Application/CommandHandlers/StartTaskHandler.php`
- Create: `src/backend/app/Modules/Onboarding/Application/CommandHandlers/CompleteTaskHandler.php`
- Create: `src/backend/app/Modules/Onboarding/Application/CommandHandlers/WaiveTaskHandler.php`

- [ ] **Step 1: AddOnboardingTaskHandler**

```php
<?php

namespace App\Modules\Onboarding\Application\Commands;

class AddOnboardingTaskCommand
{
    public function __construct(
        public readonly string $planId,
        public readonly string $ownerType,
        public readonly string $ownerId,
        public readonly string $title,
        public readonly ?string $description,
        public readonly ?string $dueDate,
        public readonly bool $requiresApproval,
        public readonly bool $isPreStart,
        public readonly int $sortOrder,
    ) {}
}
```

```php
<?php

namespace App\Modules\Onboarding\Application\CommandHandlers;

use App\Modules\Onboarding\Application\Commands\AddOnboardingTaskCommand;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlanId;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTask;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTaskId;
use App\Modules\Onboarding\Domain\Repositories\OnboardingPlanRepositoryInterface;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTaskRepositoryInterface;
use App\Modules\Onboarding\Domain\ValueObjects\OwnerType;
use App\Modules\Onboarding\Domain\ValueObjects\TaskType;
use App\Modules\Onboarding\Domain\Exceptions\OnboardingPlanNotFoundException;

class AddOnboardingTaskHandler
{
    public function __construct(
        private readonly OnboardingPlanRepositoryInterface $planRepo,
        private readonly OnboardingTaskRepositoryInterface $taskRepo,
    ) {}

    public function handle(AddOnboardingTaskCommand $command): OnboardingTask
    {
        $planId = OnboardingPlanId::fromString($command->planId);
        $plan = $this->planRepo->findById($planId);
        if (!$plan) {
            throw new OnboardingPlanNotFoundException($command->planId);
        }

        $task = OnboardingTask::create(
            OnboardingTaskId::generate(),
            $command->planId,
            TaskType::Custom,
            OwnerType::from($command->ownerType),
            $command->ownerId,
            $command->title,
            $command->description,
            $command->dueDate ? new \DateTimeImmutable($command->dueDate) : null,
            $command->requiresApproval,
            $command->isPreStart,
            $command->sortOrder,
        );

        $plan->addTask($task);
        $this->planRepo->save($plan);

        return $task;
    }
}
```

- [ ] **Step 2: RemoveOnboardingTaskHandler**

```php
<?php

namespace App\Modules\Onboarding\Application\Commands;

class RemoveOnboardingTaskCommand
{
    public function __construct(
        public readonly string $planId,
        public readonly string $taskId,
    ) {}
}
```

```php
<?php

namespace App\Modules\Onboarding\Application\CommandHandlers;

use App\Modules\Onboarding\Application\Commands\RemoveOnboardingTaskCommand;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlanId;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTaskId;
use App\Modules\Onboarding\Domain\Repositories\OnboardingPlanRepositoryInterface;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTaskRepositoryInterface;
use App\Modules\Onboarding\Domain\Exceptions\OnboardingPlanNotFoundException;

class RemoveOnboardingTaskHandler
{
    public function __construct(
        private readonly OnboardingPlanRepositoryInterface $planRepo,
        private readonly OnboardingTaskRepositoryInterface $taskRepo,
    ) {}

    public function handle(RemoveOnboardingTaskCommand $command): void
    {
        $planId = OnboardingPlanId::fromString($command->planId);
        $plan = $this->planRepo->findById($planId);
        if (!$plan) {
            throw new OnboardingPlanNotFoundException($command->planId);
        }

        $plan->removeTask($command->taskId);
        $this->taskRepo->delete(OnboardingTaskId::fromString($command->taskId));
        $this->planRepo->save($plan);
    }
}
```

- [ ] **Step 3: StartTaskHandler**

```php
<?php

namespace App\Modules\Onboarding\Application\Commands;

class StartTaskCommand
{
    public function __construct(public readonly string $taskId) {}
}
```

```php
<?php

namespace App\Modules\Onboarding\Application\CommandHandlers;

use App\Modules\Onboarding\Application\Commands\StartTaskCommand;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTaskId;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTaskRepositoryInterface;
use App\Modules\Onboarding\Domain\Exceptions\OnboardingTaskNotFoundException;

class StartTaskHandler
{
    public function __construct(
        private readonly OnboardingTaskRepositoryInterface $taskRepo,
    ) {}

    public function handle(StartTaskCommand $command): void
    {
        $task = $this->taskRepo->findById(OnboardingTaskId::fromString($command->taskId));
        if (!$task) {
            throw new OnboardingTaskNotFoundException($command->taskId);
        }

        $task->start();
        $this->taskRepo->save($task);

        foreach ($task->popRecordedEvents() as $event) {
            event($event);
        }
    }
}
```

- [ ] **Step 4: CompleteTaskHandler**

```php
<?php

namespace App\Modules\Onboarding\Application\Commands;

class CompleteTaskCommand
{
    public function __construct(
        public readonly string $taskId,
        public readonly ?string $proofFileObjectId = null,
        public readonly ?string $workflowTemplateId = null,
    ) {}
}
```

```php
<?php

namespace App\Modules\Onboarding\Application\CommandHandlers;

use App\Modules\Onboarding\Application\Commands\CompleteTaskCommand;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTaskId;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTaskRepositoryInterface;
use App\Modules\Onboarding\Domain\Exceptions\OnboardingTaskNotFoundException;
use App\Modules\Onboarding\Infrastructure\Services\TaskWorkflowService;

class CompleteTaskHandler
{
    public function __construct(
        private readonly OnboardingTaskRepositoryInterface $taskRepo,
        private readonly TaskWorkflowService $workflowService,
    ) {}

    public function handle(CompleteTaskCommand $command): void
    {
        $task = $this->taskRepo->findById(OnboardingTaskId::fromString($command->taskId));
        if (!$task) {
            throw new OnboardingTaskNotFoundException($command->taskId);
        }

        if ($task->isRequiresApproval() && $command->workflowTemplateId) {
            $requestId = $this->workflowService->startTaskApprovalWorkflow(
                $command->workflowTemplateId,
                $command->taskId,
            );
            $task->setApprovalWorkflowRequestId($requestId);
        }

        $task->complete($command->proofFileObjectId);
        $this->taskRepo->save($task);

        foreach ($task->popRecordedEvents() as $event) {
            event($event);
        }
    }
}
```

- [ ] **Step 5: WaiveTaskHandler**

```php
<?php

namespace App\Modules\Onboarding\Application\Commands;

class WaiveTaskCommand
{
    public function __construct(
        public readonly string $taskId,
        public readonly ?string $reason = null,
    ) {}
}
```

```php
<?php

namespace App\Modules\Onboarding\Application\CommandHandlers;

use App\Modules\Onboarding\Application\Commands\WaiveTaskCommand;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTaskId;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTaskRepositoryInterface;
use App\Modules\Onboarding\Domain\Exceptions\OnboardingTaskNotFoundException;

class WaiveTaskHandler
{
    public function __construct(
        private readonly OnboardingTaskRepositoryInterface $taskRepo,
    ) {}

    public function handle(WaiveTaskCommand $command): void
    {
        $task = $this->taskRepo->findById(OnboardingTaskId::fromString($command->taskId));
        if (!$task) {
            throw new OnboardingTaskNotFoundException($command->taskId);
        }

        $task->waive($command->reason);
        $this->taskRepo->save($task);

        foreach ($task->popRecordedEvents() as $event) {
            event($event);
        }
    }
}
```

- [ ] **Step 6: Commit**

```bash
git add src/backend/app/Modules/Onboarding/Application/Commands/*.php src/backend/app/Modules/Onboarding/Application/CommandHandlers/*.php
git commit -m "feat(onboarding): add task lifecycle command handlers"
```

---

### Task 13: Queries

**Files:**
- Create: `src/backend/app/Modules/Onboarding/Application/Queries/ListPlansQuery.php`
- Create: `src/backend/app/Modules/Onboarding/Application/Queries/ListTemplatesQuery.php`
- Create: `src/backend/app/Modules/Onboarding/Application/Queries/ListTasksQuery.php`
- Create: `src/backend/app/Modules/Onboarding/Application/QueryHandlers/ListPlansHandler.php`
- Create: `src/backend/app/Modules/Onboarding/Application/QueryHandlers/ListTemplatesHandler.php`
- Create: `src/backend/app/Modules/Onboarding/Application/QueryHandlers/ListTasksHandler.php`

- [ ] **Step 1-3: Query classes**

```php
<?php

namespace App\Modules\Onboarding\Application\Queries;

class ListTemplatesQuery
{
    public function __construct(
        public readonly ?string $departmentId = null,
        public readonly ?string $positionId = null,
        public readonly ?string $locationId = null,
        public readonly ?string $employmentType = null,
    ) {}
}
```

```php
<?php

namespace App\Modules\Onboarding\Application\Queries;

class ListPlansQuery
{
    public function __construct(
        public readonly ?string $employeeId = null,
    ) {}
}
```

```php
<?php

namespace App\Modules\Onboarding\Application\Queries;

class ListTasksQuery
{
    public function __construct(
        public readonly string $planId,
    ) {}
}
```

- [ ] **Step 4-6: Query handlers that use repositories to fetch and return domain objects.**
```php
<?php

namespace App\Modules\Onboarding\Application\QueryHandlers;

use App\Modules\Onboarding\Application\Queries\ListTemplatesQuery;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTemplateRepositoryInterface;

class ListTemplatesHandler
{
    public function __construct(
        private readonly OnboardingTemplateRepositoryInterface $templateRepo,
    ) {}

    public function handle(ListTemplatesQuery $query): array
    {
        if ($query->departmentId || $query->positionId || $query->locationId || $query->employmentType) {
            return $this->templateRepo->findMatching(
                $query->departmentId,
                $query->positionId,
                $query->locationId,
                $query->employmentType,
            );
        }
        return $this->templateRepo->all();
    }
}
```

```php
<?php

namespace App\Modules\Onboarding\Application\QueryHandlers;

use App\Modules\Onboarding\Application\Queries\ListPlansQuery;
use App\Modules\Onboarding\Domain\Repositories\OnboardingPlanRepositoryInterface;

class ListPlansHandler
{
    public function __construct(
        private readonly OnboardingPlanRepositoryInterface $planRepo,
    ) {}

    public function handle(ListPlansQuery $query): array
    {
        if ($query->employeeId) {
            return $this->planRepo->findByEmployeeId($query->employeeId);
        }
        return $this->planRepo->all();
    }
}
```

```php
<?php

namespace App\Modules\Onboarding\Application\QueryHandlers;

use App\Modules\Onboarding\Application\Queries\ListTasksQuery;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTaskRepositoryInterface;

class ListTasksHandler
{
    public function __construct(
        private readonly OnboardingTaskRepositoryInterface $taskRepo,
    ) {}

    public function handle(ListTasksQuery $query): array
    {
        return $this->taskRepo->findByPlanId($query->planId);
    }
}
```

- [ ] **Step 7: Commit**

```bash
git add src/backend/app/Modules/Onboarding/Application/Queries/*.php src/backend/app/Modules/Onboarding/Application/QueryHandlers/*.php
git commit -m "feat(onboarding): add query objects and handlers"
```

---

### Task 14: Infrastructure Services (Workflow + Notification)

**Files:**
- Create: `src/backend/app/Modules/Onboarding/Infrastructure/Services/PlanWorkflowService.php`
- Create: `src/backend/app/Modules/Onboarding/Infrastructure/Services/TaskWorkflowService.php`
- Create: `src/backend/app/Modules/Onboarding/Infrastructure/Services/NotificationService.php`

- [ ] **Step 1: PlanWorkflowService**

```php
<?php

namespace App\Modules\Onboarding\Infrastructure\Services;

class PlanWorkflowService
{
    public function startWorkflow(string $workflowTemplateId, string $subjectType, string $subjectId): string
    {
        // Calls Workflow BC to create a workflow request
        // Returns the workflow_request_id (UUID string)
        // Implementation depends on Workflow BC API — stub for now:
        // $workflowRequest = WorkflowRequest::create([...]);
        // return $workflowRequest->id;
        throw new \RuntimeException('Workflow BC integration not yet wired');
    }
}
```

- [ ] **Step 2: TaskWorkflowService**


```php
<?php

namespace App\Modules\Onboarding\Infrastructure\Services;

class TaskWorkflowService
{
    public function startTaskApprovalWorkflow(string $workflowTemplateId, string $taskId): string
    {
        // Calls Workflow BC to create workflow request for task approval
        // Returns the approval_workflow_request_id (UUID string)
        // Stub until Workflow BC API is finalized:
        throw new \RuntimeException("Workflow BC integration not yet wired");
    }
}
```


- [ ] **Step 3: NotificationService**

```php
<?php

namespace App\Modules\Onboarding\Infrastructure\Services;

class NotificationService
{
    public function notifyTaskAssigned(string $ownerType, string $ownerId, string $taskTitle, \DateTimeImmutable $dueDate): void
    {
        // Direct call to Notification BC or emit event
        event(new \App\Modules\Onboarding\Domain\Events\OnboardingTaskAssigned(
            null, null, $ownerType, $ownerId, $dueDate
        ));
    }

    public function notifyTaskCompleted(string $taskId, string $planId): void
    {
        event(new \App\Modules\Onboarding\Domain\Events\OnboardingTaskCompleted(null, $planId, null));
    }

    public function notifyTaskOverdue(string $taskId, string $planId, string $ownerType, string $ownerId): void
    {
        // Could be called by a scheduler command checking due dates
    }

    public function notifyPlanCompleted(string $planId, string $employeeId): void
    {
        event(new \App\Modules\Onboarding\Domain\Events\OnboardingCompleted($planId, $employeeId));
    }
}
```

- [ ] **Step 4: Commit**

```bash
git add src/backend/app/Modules/Onboarding/Infrastructure/Services/*.php
git commit -m "feat(onboarding): add workflow and notification services"
```

---

### Task 15: Listener + Jobs (Recruitment integration + Workflow callbacks)

**Files:**
- Create: `src/backend/app/Modules/Onboarding/Infrastructure/Listeners/CandidateHiredListener.php`
- Create: `src/backend/app/Modules/Onboarding/Infrastructure/Jobs/PlanCompletionApprovedJob.php`
- Create: `src/backend/app/Modules/Onboarding/Infrastructure/Jobs/TaskApprovedJob.php`

- [ ] **Step 1: CandidateHiredListener**

```php
<?php

namespace App\Modules\Onboarding\Infrastructure\Listeners;

use App\Modules\Onboarding\Application\Commands\CreateOnboardingPlanCommand;
use App\Modules\Onboarding\Application\CommandHandlers\CreateOnboardingPlanHandler;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTemplateRepositoryInterface;

class CandidateHiredListener
{
    public function __construct(
        private readonly OnboardingTemplateRepositoryInterface $templateRepo,
        private readonly CreateOnboardingPlanHandler $createPlanHandler,
    ) {}

    public function handle($event): void
    {
        // $event is CandidateHired from Recruitment BC
        // Expected properties: employeeId, candidateId, departmentId, positionId, locationId, employmentType, startDate
        $templates = $this->templateRepo->findMatching(
            $event->departmentId ?? null,
            $event->positionId ?? null,
            $event->locationId ?? null,
            $event->employmentType ?? null,
        );

        $templateId = !empty($templates) ? $templates[0]->getId()->toString() : null;

        $command = new CreateOnboardingPlanCommand(
            employeeId: $event->employeeId,
            candidateId: $event->candidateId ?? null,
            templateId: $templateId,
            startDate: $event->startDate ?? date('Y-m-d'),
        );

        $this->createPlanHandler->handle($command);
    }
}
```

Register in `AppServiceProvider` or `EventServiceProvider`:
```php
// In EventServiceProvider $listen array or boot()
\Illuminate\Support\Facades\Event::listen(
    'App\Modules\Recruitment\Domain\Events\CandidateHired', // event class from Recruitment
    \App\Modules\Onboarding\Infrastructure\Listeners\CandidateHiredListener::class,
);
```

- [ ] **Step 2: PlanCompletionApprovedJob**

```php
<?php

namespace App\Modules\Onboarding\Infrastructure\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlanId;
use App\Modules\Onboarding\Domain\Repositories\OnboardingPlanRepositoryInterface;

class PlanCompletionApprovedJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        private readonly string $workflowRequestId,
    ) {}

    public function handle(OnboardingPlanRepositoryInterface $planRepo): void
    {
        $plan = $planRepo->findByWorkflowRequestId($this->workflowRequestId);
        if (!$plan) {
            return;
        }

        $plan->markWorkflowApproved();
        $planRepo->save($plan);

        foreach ($plan->popRecordedEvents() as $event) {
            event($event);
        }
    }
}
```

- [ ] **Step 3: TaskApprovedJob**


```php
<?php

namespace App\Modules\Onboarding\Infrastructure\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTaskRepositoryInterface;

class TaskApprovedJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        private readonly string $approvalWorkflowRequestId,
    ) {}

    public function handle(OnboardingTaskRepositoryInterface $taskRepo): void
    {
        $task = $taskRepo->findByApprovalWorkflowRequestId($this->approvalWorkflowRequestId);
        if (!$task) {
            return;
        }

        $task->markApproved();
        $taskRepo->save($task);

        foreach ($task->popRecordedEvents() as $event) {
            event($event);
        }
    }
}
```


- [ ] **Step 4: Commit**

```bash
git add src/backend/app/Modules/Onboarding/Infrastructure/Listeners/*.php src/backend/app/Modules/Onboarding/Infrastructure/Jobs/*.php
git commit -m "feat(onboarding): add CandidateHired listener and workflow approval jobs"
```

---

### Task 16: Controllers + HTTP Layer

**Files:**
- Create: `src/backend/app/Modules/Onboarding/Infrastructure/Http/Controllers/OnboardingTemplateController.php`
- Create: `src/backend/app/Modules/Onboarding/Infrastructure/Http/Controllers/OnboardingPlanController.php`
- Create: `src/backend/app/Modules/Onboarding/Infrastructure/Http/Controllers/OnboardingTaskController.php`

- [ ] **Step 1-3: Controllers**

Each controller:
- Injects command bus or specific handlers
- Validates request via FormRequest (or inline)
- Dispatches command → collects result
- Returns JSON response with resource

```php
// OnboardingTemplateController snippet
class OnboardingTemplateController extends Controller
{
    public function __construct(
        private readonly CreateOnboardingTemplateHandler $createHandler,
        private readonly UpdateOnboardingTemplateHandler $updateHandler,
        private readonly ListTemplatesHandler $listHandler,
    ) {}

    public function index(ListTemplatesRequest $request): JsonResponse
    {
        $query = new ListTemplatesQuery(
            $request->input('department_id'),
            $request->input('position_id'),
            $request->input('location_id'),
            $request->input('employment_type'),
        );
        $templates = $this->listHandler->handle($query);
        return response()->json(['data' => $templates]);
    }

    public function store(CreateOnboardingTemplateRequest $request): JsonResponse
    {
        $command = new CreateOnboardingTemplateCommand(
            code: $request->input('code'),
            name: $request->input('name'),
            rules: $request->input('rules', []),
        );
        $template = $this->createHandler->handle($command);
        return response()->json(['data' => $template], 201);
    }


    public function update(UpdateOnboardingTemplateRequest $request, string $id): JsonResponse
    {
        $command = new UpdateOnboardingTemplateCommand(
            id: $id,
            code: $request->input('code'),
            name: $request->input('name'),
            rules: $request->input('rules', []),
        );
        $template = $this->updateHandler->handle($command);
        return response()->json(['data' => $template]);
    }

    public function destroy(string $id): JsonResponse
    {
        $templateId = OnboardingTemplateId::fromString($id);
        $template = $this->templateRepo->findById($templateId);
        if (!$template) {
            throw new OnboardingTemplateNotFoundException($id);
        }
        $template->disable();
        $this->templateRepo->save($template);
        return response()->json(null, 204);
    }
}
```

Implement all 3 controllers matching the spec's API endpoints table.

- [ ] **Step 4: Create FormRequest classes for validation**

Create under `Infrastructure/Http/Requests/`:
- `CreateOnboardingTemplateRequest` — validates code (unique, max 50), name (required, max 255), rules (array)
- `CreateOnboardingPlanRequest` — validates employee_id (required, uuid), template_id (nullable, uuid), start_date (required, date)
- `AddOnboardingTaskRequest` — validates title (required), owner_type (required, in: department,user_role), etc.

- [ ] **Step 5: Create JSON resource classes (optional, can use simple array mapping)**

Create under `Infrastructure/Http/Resources/`:
- `OnboardingTemplateResource`
- `OnboardingPlanResource` (includes tasks relation)
- `OnboardingTaskResource`

- [ ] **Step 6: Commit**

```bash
git add src/backend/app/Modules/Onboarding/Infrastructure/Http/Controllers/*.php src/backend/app/Modules/Onboarding/Infrastructure/Http/Requests/*.php src/backend/app/Modules/Onboarding/Infrastructure/Http/Resources/*.php
git commit -m "feat(onboarding): add HTTP controllers, requests, and resources"
```

---

### Task 17: Routes + Route Registration

**Files:**
- Create: `src/backend/app/Modules/Onboarding/Routes/api.php`
- Modify: `src/backend/routes/api.php`

- [ ] **Step 1: Module routes file**

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Onboarding\Infrastructure\Http\Controllers\OnboardingTemplateController;
use App\Modules\Onboarding\Infrastructure\Http\Controllers\OnboardingPlanController;
use App\Modules\Onboarding\Infrastructure\Http\Controllers\OnboardingTaskController;

Route::prefix('v1/onboarding')
    ->middleware(['auth:sanctum'])
    ->group(function () {

        // Templates
        Route::get('templates', [OnboardingTemplateController::class, 'index'])
            ->middleware('permission:onboarding.template.view');
        Route::post('templates', [OnboardingTemplateController::class, 'store'])
            ->middleware('permission:onboarding.template.create');
        Route::get('templates/{id}', [OnboardingTemplateController::class, 'show'])
            ->middleware('permission:onboarding.template.view');
        Route::patch('templates/{id}', [OnboardingTemplateController::class, 'update'])
            ->middleware('permission:onboarding.template.update');
        Route::delete('templates/{id}', [OnboardingTemplateController::class, 'destroy'])
            ->middleware('permission:onboarding.template.delete');

        // Plans
        Route::get('plans', [OnboardingPlanController::class, 'index'])
            ->middleware('permission:onboarding.plan.view');
        Route::post('plans', [OnboardingPlanController::class, 'store'])
            ->middleware('permission:onboarding.plan.create');
        Route::get('plans/{id}', [OnboardingPlanController::class, 'show'])
            ->middleware('permission:onboarding.plan.view');
        Route::patch('plans/{id}', [OnboardingPlanController::class, 'update'])
            ->middleware('permission:onboarding.plan.update');
        Route::post('plans/{id}/activate', [OnboardingPlanController::class, 'activate'])
            ->middleware('permission:onboarding.plan.activate');
        Route::post('plans/{id}/cancel', [OnboardingPlanController::class, 'cancel'])
            ->middleware('permission:onboarding.plan.cancel');
        Route::post('plans/{id}/complete', [OnboardingPlanController::class, 'complete'])
            ->middleware('permission:onboarding.plan.complete');

        // Tasks
        Route::get('plans/{planId}/tasks', [OnboardingTaskController::class, 'index'])
            ->middleware('permission:onboarding.task.view');
        Route::post('plans/{planId}/tasks', [OnboardingTaskController::class, 'store'])
            ->middleware('permission:onboarding.task.create');
        Route::get('tasks/{id}', [OnboardingTaskController::class, 'show'])
            ->middleware('permission:onboarding.task.view');
        Route::patch('tasks/{id}', [OnboardingTaskController::class, 'update'])
            ->middleware('permission:onboarding.task.update');
        Route::post('tasks/{id}/start', [OnboardingTaskController::class, 'start'])
            ->middleware('permission:onboarding.task.start');
        Route::post('tasks/{id}/complete', [OnboardingTaskController::class, 'complete'])
            ->middleware('permission:onboarding.task.complete');
        Route::post('tasks/{id}/waive', [OnboardingTaskController::class, 'waive'])
            ->middleware('permission:onboarding.task.waive');
    });
```

- [ ] **Step 2: Register in routes/api.php**

Add to `src/backend/routes/api.php`:

```php
require __DIR__ . '/../app/Modules/Onboarding/Routes/api.php';
```

- [ ] **Step 3: Commit**

```bash
git add src/backend/app/Modules/Onboarding/Routes/api.php src/backend/routes/api.php
git commit -m "feat(onboarding): add routes and register in api.php"
```

---

### Task 18: Permission Seeder

**File:**
- Create: `src/backend/app/Modules/Onboarding/Infrastructure/Seeders/OnboardingPermissionSeeder.php`

- [ ] **Step 1: Permission seeder**

```php
<?php

namespace App\Modules\Onboarding\Infrastructure\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Identity\Domain\Entities\Permission;
use App\Modules\Identity\Domain\Entities\Role;

class OnboardingPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'onboarding.template.view',
            'onboarding.template.create',
            'onboarding.template.update',
            'onboarding.template.delete',
            'onboarding.plan.view',
            'onboarding.plan.create',
            'onboarding.plan.update',
            'onboarding.plan.activate',
            'onboarding.plan.cancel',
            'onboarding.plan.complete',
            'onboarding.task.view',
            'onboarding.task.create',
            'onboarding.task.update',
            'onboarding.task.start',
            'onboarding.task.complete',
            'onboarding.task.waive',
        ];

        foreach ($permissions as $code) {
            Permission::firstOrCreate(['code' => $code], ['name' => $code]);
        }

        // Assign all to Admin and HR Manager
        $adminRole = Role::where('code', 'admin')->first();
        $hrManagerRole = Role::where('code', 'hr_manager')->first();
        $hrStaffRole = Role::where('code', 'hr_staff')->first();

        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }
        if ($hrManagerRole) {
            $hrManagerRole->givePermissionTo($permissions);
        }
        if ($hrStaffRole) {
            $hrStaffRole->givePermissionTo($permissions);
        }

        // Department Manager gets view + task action permissions
        $deptMgrRole = Role::where('code', 'department_manager')->first();
        if ($deptMgrRole) {
            $deptMgrRole->givePermissionTo([
                'onboarding.plan.view',
                'onboarding.plan.complete',
                'onboarding.task.view',
                'onboarding.task.start',
                'onboarding.task.complete',
                'onboarding.task.waive',
            ]);
        }

        // Employee gets self-task permissions
        $employeeRole = Role::where('code', 'employee')->first();
        if ($employeeRole) {
            $employeeRole->givePermissionTo([
                'onboarding.task.view',
                'onboarding.task.start',
                'onboarding.task.waive',
            ]);
        }
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add src/backend/app/Modules/Onboarding/Infrastructure/Seeders/*
git commit -m "feat(onboarding): add permission seeder"
```

---

### Task 19: Domain Unit Tests

**Files:**
- Create: `src/backend/tests/Unit/Modules/Onboarding/OnboardingPlanStatusTest.php`
- Create: `src/backend/tests/Unit/Modules/Onboarding/OnboardingTaskStatusTest.php`
- Create: `src/backend/tests/Unit/Modules/Onboarding/OnboardingPlanTest.php`
- Create: `src/backend/tests/Unit/Modules/Onboarding/OnboardingTaskTest.php`
- Create: `src/backend/tests/Unit/Modules/Onboarding/OnboardingTemplateTest.php`
- Create: `src/backend/tests/Unit/Modules/Onboarding/TemplateRulesTest.php`

- [ ] **Step 1-6: Write domain unit tests**

```php
<?php

namespace Tests\Unit\Modules\Onboarding;

use App\Modules\Onboarding\Domain\ValueObjects\OnboardingPlanStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OnboardingPlanStatusTest extends TestCase
{
    #[Test]
    public function it_allows_draft_to_active(): void
    {
        $this->assertTrue(OnboardingPlanStatus::Draft->canTransitionTo(OnboardingPlanStatus::Active));
    }

    #[Test]
    public function it_allows_draft_to_cancelled(): void
    {
        $this->assertTrue(OnboardingPlanStatus::Draft->canTransitionTo(OnboardingPlanStatus::Cancelled));
    }

    #[Test]
    public function it_blocks_draft_to_completed(): void
    {
        $this->assertFalse(OnboardingPlanStatus::Draft->canTransitionTo(OnboardingPlanStatus::Completed));
    }

    #[Test]
    public function it_blocks_terminal_transitions(): void
    {
        $this->assertFalse(OnboardingPlanStatus::Completed->canTransitionTo(OnboardingPlanStatus::Active));
        $this->assertFalse(OnboardingPlanStatus::Cancelled->canTransitionTo(OnboardingPlanStatus::Draft));
    }
}
```

Similarly for `OnboardingTaskStatusTest` (tests all 4 states transitions), `TemplateRulesTest` (tests matches() with various filters), `OnboardingPlanTest` (activate, cancel, complete, complete with pending tasks, markWorkflowApproved, addTask, removeTask), `OnboardingTaskTest` (start, complete, complete with approval, waive, terminal guard), `OnboardingTemplateTest` (create, update, disable, matches, generatePlan creates correct tasks).

- [ ] **Step 7: Commit**

```bash
git add src/backend/tests/Unit/Modules/Onboarding/
git commit -m "test(onboarding): add domain unit tests"
```

---

### Task 20: Feature Tests

**Files:**
- Create: `src/backend/tests/Feature/Modules/Onboarding/OnboardingApiTest.php`

- [ ] **Step 1: Write feature tests for the onboarding API**

```php
<?php

namespace Tests\Feature\Modules\Onboarding;

use Tests\TestCase;
use App\Modules\Identity\Domain\Entities\User;
use App\Modules\Onboarding\Domain\ValueObjects\OnboardingPlanStatus;
use App\Modules\Onboarding\Domain\ValueObjects\OnboardingTaskStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class OnboardingApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->adminToken = $this->admin->createToken('test')->plainTextToken;
    }

    #[Test]
    public function auth_required_for_all_endpoints(): void
    {
        $this->getJson('/api/v1/onboarding/templates')->assertUnauthorized();
        $this->postJson('/api/v1/onboarding/templates', [])->assertUnauthorized();
        $this->getJson('/api/v1/onboarding/plans')->assertUnauthorized();
        $this->postJson('/api/v1/onboarding/plans', [])->assertUnauthorized();
        $this->getJson('/api/v1/onboarding/plans/1/tasks')->assertUnauthorized();
    }

    #[Test]
    public function happy_path_template_plan_tasks_complete(): void
    {
        $headers = ['Authorization' => 'Bearer ' . $this->adminToken];

        // Create template
        $templateResponse = $this->postJson('/api/v1/onboarding/templates', [
            'code' => 'hr-default',
            'name' => 'HR Default Onboarding',
            'rules' => [
                'tasks' => [
                    [
                        'title' => 'Prepare laptop',
                        'owner_type' => 'department',
                        'owner_id' => 'it',
                        'due_days' => -7,
                        'requires_approval' => true,
                        'is_pre_start' => true,
                        'sort_order' => 1,
                    ],
                ],
            ],
        ], $headers);

        $templateResponse->assertCreated();
        $templateId = $templateResponse->json('data.id');

        // Create plan
        $planResponse = $this->postJson('/api/v1/onboarding/plans', [
            'employee_id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
            'template_id' => $templateId,
            'start_date' => '2026-07-15',
        ], $headers);

        $planResponse->assertCreated();
        $planId = $planResponse->json('data.id');

        // Activate plan
        $this->postJson("/api/v1/onboarding/plans/{$planId}/activate", [], $headers)
            ->assertOk();

        // Add custom task
        $taskResponse = $this->postJson("/api/v1/onboarding/plans/{$planId}/tasks", [
            'title' => 'Welcome email',
            'owner_type' => 'user_role',
            'owner_id' => 'hr',
            'requires_approval' => false,
            'is_pre_start' => false,
            'sort_order' => 10,
        ], $headers);

        $taskResponse->assertCreated();
        $taskId = $taskResponse->json('data.id');

        // Start task
        $this->postJson("/api/v1/onboarding/tasks/{$taskId}/start", [], $headers)
            ->assertOk();

        // Complete task
        $this->postJson("/api/v1/onboarding/tasks/{$taskId}/complete", [], $headers)
            ->assertOk();

        // Complete plan
        $this->postJson("/api/v1/onboarding/plans/{$planId}/complete", [], $headers)
            ->assertOk();
    }

    #[Test]
    public function plan_activation_fails_with_no_tasks(): void
    {
        $headers = ['Authorization' => 'Bearer ' . $this->adminToken];

        $planResponse = $this->postJson('/api/v1/onboarding/plans', [
            'employee_id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
            'start_date' => '2026-07-15',
        ], $headers);

        $planId = $planResponse->json('data.id');

        $this->postJson("/api/v1/onboarding/plans/{$planId}/activate", [], $headers)
            ->assertStatus(422);
    }

    #[Test]
    public function plan_complete_fails_with_pending_tasks(): void
    {
        $headers = ['Authorization' => 'Bearer ' . $this->adminToken];

        $templateResponse = $this->postJson('/api/v1/onboarding/templates', [
            'code' => 'test-pending',
            'name' => 'Test',
            'rules' => [
                'tasks' => [
                    [
                        'title' => 'Mandatory task',
                        'owner_type' => 'user_role',
                        'owner_id' => 'hr',
                        'due_days' => 0,
                        'requires_approval' => false,
                        'is_pre_start' => false,
                        'sort_order' => 1,
                    ],
                ],
            ],
        ], $headers);

        $planResponse = $this->postJson('/api/v1/onboarding/plans', [
            'employee_id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
            'template_id' => $templateResponse->json('data.id'),
            'start_date' => '2026-07-15',
        ], $headers);

        $planId = $planResponse->json('data.id');
        $this->postJson("/api/v1/onboarding/plans/{$planId}/activate", [], $headers)->assertOk();

        // Try completing plan without completing the mandatory task
        $this->postJson("/api/v1/onboarding/plans/{$planId}/complete", [], $headers)
            ->assertStatus(422);
    }
}
```

- [ ] **Step 2: Permission boundary test**

```php
#[Test]
public function permission_denied_for_unauthorized_role(): void
{
    $employee = User::factory()->create(['role' => 'employee']);
    $token = $employee->createToken('test')->plainTextToken;

    $this->postJson('/api/v1/onboarding/templates', [
        'code' => 'test',
        'name' => 'Test',
    ], ['Authorization' => 'Bearer ' . $token])
        ->assertForbidden();
}
```

- [ ] **Step 3: Commit**

```bash
git add src/backend/tests/Feature/Modules/Onboarding/
git commit -m "test(onboarding): add feature integration tests"
```

---

### Task 21: Auto-load discovery + Final verification

**Files:**
- Ensure `composer.json` has PSR-4 autoloading for `App\Modules\Onboarding` (check existing `App\Modules\*` pattern)
- Verify routes load, migrations run, tests pass

- [ ] **Step 1: Verify autoloading**

Check `composer.json` for existing `App\Modules\` autoload entry. If modules are already loaded via `classmap` or `psr-4`, confirm no change needed. If not, add:

```json
"autoload": {
    "psr-4": {
        "App\\Modules\\": "app/Modules/"
    }
}
```

Ensure existing modules (Recruitment, Attendance, etc.) already have this.

- [ ] **Step 2: Run migrations**

```bash
docker compose run --rm app php artisan migrate
```

- [ ] **Step 3: Run full test suite**

```bash
docker compose run --rm app php artisan test --compact
```

Expected: All tests pass (including existing tests from other modules).

- [ ] **Step 4: Run targeted Onboarding tests**

```bash
docker compose run --rm app php artisan test tests/Unit/Modules/Onboarding tests/Feature/Modules/Onboarding --compact
```

Expected: All onboarding tests pass.

- [ ] **Step 5: Spec acceptance review**

Compare against spec AC1-12. Verify:
- ✅ DDD layout matches spec
- ✅ Templates support filtering
- ✅ Plan from template auto-generates tasks
- ✅ Workflow integration for plan sign-off
- ✅ Task-level approval support
- ✅ Recruitment event listener
- ✅ Manual plan creation
- ✅ Pre-start/post-start flags
- ✅ API routes + permissions seeded
- ✅ Tests exist

- [ ] **Step 6: Final commit**

```bash
git add -A
git commit -m "feat(onboarding): complete Onboarding module implementation"
```
