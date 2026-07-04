# Workflow Engine B1 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a reusable workflow engine with condition-based routing, pluggable assignee resolution, delegation support, and one real integration path for Leave.

**Architecture:** Extend the existing Workflow module with a `WorkflowEngine` service, a pure `ConditionEvaluator`, resolver/provider registries, and delegation persistence. Keep handlers thin, preserve existing aggregates where possible, and integrate Leave through event listeners and subject-provider contracts instead of direct coupling.

**Tech Stack:** Laravel, PHP 8.3, PostgreSQL JSONB, existing modular monolith conventions, PHPUnit feature/unit tests.

---

## File Structure

### Workflow module files to create

- Create: `src/backend/app/Modules/Workflow/Application/Services/WorkflowEngine.php`
- Create: `src/backend/app/Modules/Workflow/Application/Services/ConditionEvaluator.php`
- Create: `src/backend/app/Modules/Workflow/Application/Services/ResolverRegistry.php`
- Create: `src/backend/app/Modules/Workflow/Application/Services/SubjectDataProviderRegistry.php`
- Create: `src/backend/app/Modules/Workflow/Application/Services/DelegationResolver.php`
- Create: `src/backend/app/Modules/Workflow/Application/Contracts/AssigneeResolver.php`
- Create: `src/backend/app/Modules/Workflow/Application/Contracts/SubjectDataProvider.php`
- Create: `src/backend/app/Modules/Workflow/Application/Services/DelegationResult.php`
- Create: `src/backend/app/Modules/Workflow/Application/Resolvers/SpecificUserResolver.php`
- Create: `src/backend/app/Modules/Workflow/Application/Resolvers/RoleResolver.php`
- Create: `src/backend/app/Modules/Workflow/Application/Resolvers/DirectManagerResolver.php`
- Create: `src/backend/app/Modules/Workflow/Application/Resolvers/DepartmentHeadResolver.php`
- Create: `src/backend/app/Modules/Workflow/Application/Resolvers/RoleInDepartmentResolver.php`
- Create: `src/backend/app/Modules/Workflow/Application/Queries/ListWorkflowDelegationsQuery.php`
- Create: `src/backend/app/Modules/Workflow/Application/QueryHandlers/ListWorkflowDelegationsHandler.php`
- Create: `src/backend/app/Modules/Workflow/Application/Commands/CreateWorkflowDelegationCommand.php`
- Create: `src/backend/app/Modules/Workflow/Application/Commands/RevokeWorkflowDelegationCommand.php`
- Create: `src/backend/app/Modules/Workflow/Application/CommandHandlers/CreateWorkflowDelegationHandler.php`
- Create: `src/backend/app/Modules/Workflow/Application/CommandHandlers/RevokeWorkflowDelegationHandler.php`
- Create: `src/backend/app/Modules/Workflow/Domain/Aggregates/WorkflowDelegation/WorkflowDelegation.php`
- Create: `src/backend/app/Modules/Workflow/Domain/Aggregates/WorkflowDelegation/WorkflowDelegationId.php`
- Create: `src/backend/app/Modules/Workflow/Domain/Repositories/WorkflowDelegationRepositoryInterface.php`
- Create: `src/backend/app/Modules/Workflow/Domain/Exceptions/WorkflowResolverNotFoundException.php`
- Create: `src/backend/app/Modules/Workflow/Domain/Exceptions/WorkflowSubjectProviderNotFoundException.php`
- Create: `src/backend/app/Modules/Workflow/Domain/Exceptions/WorkflowDelegationConflictException.php`
- Create: `src/backend/app/Modules/Workflow/Domain/Exceptions/WorkflowDelegationNotFoundException.php`
- Create: `src/backend/app/Modules/Workflow/Domain/Exceptions/WorkflowConditionEvaluationException.php`
- Create: `src/backend/app/Modules/Workflow/Infrastructure/Http/Controllers/WorkflowDelegationController.php`
- Create: `src/backend/app/Modules/Workflow/Infrastructure/Http/Controllers/Actions/ListWorkflowDelegationController.php`
- Create: `src/backend/app/Modules/Workflow/Infrastructure/Http/Controllers/Actions/StoreWorkflowDelegationController.php`
- Create: `src/backend/app/Modules/Workflow/Infrastructure/Http/Controllers/Actions/DeleteWorkflowDelegationController.php`
- Create: `src/backend/app/Modules/Workflow/Infrastructure/Http/Requests/CreateWorkflowDelegationRequest.php`
- Create: `src/backend/app/Modules/Workflow/Infrastructure/Http/Resources/WorkflowDelegationResource.php`
- Create: `src/backend/app/Modules/Workflow/Infrastructure/Persistence/Eloquent/WorkflowDelegationModel.php`
- Create: `src/backend/app/Modules/Workflow/Infrastructure/Persistence/Repositories/EloquentWorkflowDelegationRepository.php`
- Create: `src/backend/app/Modules/Workflow/Infrastructure/Providers/WorkflowServiceProvider.php`

### Workflow module files to modify

- Modify: `src/backend/app/Modules/Workflow/Application/CommandHandlers/SubmitWorkflowRequestHandler.php`
- Modify: `src/backend/app/Modules/Workflow/Application/CommandHandlers/ApproveWorkflowStepHandler.php`
- Modify: `src/backend/app/Modules/Workflow/Application/CommandHandlers/RejectWorkflowStepHandler.php`
- Modify: `src/backend/app/Modules/Workflow/Application/CommandHandlers/ReturnWorkflowForEditHandler.php`
- Modify: `src/backend/app/Modules/Workflow/Domain/Aggregates/WorkflowRequest/WorkflowRequest.php`
- Modify: `src/backend/app/Modules/Workflow/Domain/Aggregates/WorkflowRequest/WorkflowAction.php`
- Modify: `src/backend/app/Modules/Workflow/Domain/Aggregates/WorkflowTemplate/WorkflowStep.php`
- Modify: `src/backend/app/Modules/Workflow/Infrastructure/Http/Controllers/WorkflowTemplateController.php`
- Modify: `src/backend/app/Modules/Workflow/Infrastructure/Http/Controllers/WorkflowRequestController.php`
- Modify: `src/backend/app/Modules/Workflow/Infrastructure/Http/Requests/CreateWorkflowTemplateRequest.php`
- Modify: `src/backend/app/Modules/Workflow/Infrastructure/Http/Resources/WorkflowRequestResource.php`
- Modify: `src/backend/app/Modules/Workflow/Infrastructure/Http/Resources/WorkflowTemplateResource.php`
- Modify: `src/backend/app/Modules/Workflow/Infrastructure/Persistence/Eloquent/WorkflowRequestActionModel.php`
- Modify: `src/backend/app/Modules/Workflow/Infrastructure/Persistence/Eloquent/WorkflowRequestModel.php`
- Modify: `src/backend/app/Modules/Workflow/Infrastructure/Persistence/Eloquent/WorkflowTemplateStepModel.php`
- Modify: `src/backend/app/Modules/Workflow/Infrastructure/Persistence/Repositories/EloquentWorkflowRequestRepository.php`
- Modify: `src/backend/app/Modules/Workflow/Infrastructure/Persistence/Repositories/EloquentWorkflowTemplateRepository.php`
- Modify: `src/backend/app/Modules/Workflow/Routes/api.php`

### Leave integration files to create

- Create: `src/backend/app/Modules/Leave/Application/Workflow/LeaveRequestSubjectProvider.php`
- Create: `src/backend/app/Modules/Leave/Application/Workflow/Listeners/SyncLeaveRequestOnWorkflowApproved.php`
- Create: `src/backend/app/Modules/Leave/Application/Workflow/Listeners/SyncLeaveRequestOnWorkflowRejected.php`

### Leave integration files to modify

- Modify: `src/backend/app/Modules/Leave/Infrastructure/Http/Controllers/LeaveRequestController.php`
- Modify: `src/backend/app/Modules/Leave/Infrastructure/Http/Requests/SubmitLeaveRequest.php`
- Modify: `src/backend/app/Modules/Leave/Infrastructure/Persistence/Eloquent/LeaveTypeModel.php`
- Modify: `src/backend/app/Modules/Leave/Infrastructure/Persistence/Eloquent/LeaveRequestModel.php`
- Modify: `src/backend/app/Modules/Leave/Infrastructure/Http/Resources/LeaveRequestResource.php`
- Modify: `src/backend/app/Providers/AppServiceProvider.php`

### Migrations to create

- Create: `src/backend/database/migrations/2026_07_04_000001_add_engine_fields_to_workflow_template_steps.php`
- Create: `src/backend/database/migrations/2026_07_04_000002_add_context_to_workflow_requests.php`
- Create: `src/backend/database/migrations/2026_07_04_000003_add_resolution_metadata_to_workflow_request_actions.php`
- Create: `src/backend/database/migrations/2026_07_04_000004_create_workflow_delegations_table.php`
- Create: `src/backend/database/migrations/2026_07_04_000005_add_workflow_template_code_to_leave_types.php`

### Tests to create

- Create: `src/backend/tests/Unit/Modules/Workflow/ConditionEvaluatorTest.php`
- Create: `src/backend/tests/Unit/Modules/Workflow/DelegationResolverTest.php`
- Create: `src/backend/tests/Unit/Modules/Workflow/ResolverRegistryTest.php`
- Create: `src/backend/tests/Unit/Modules/Workflow/SpecificUserResolverTest.php`
- Create: `src/backend/tests/Unit/Modules/Workflow/DirectManagerResolverTest.php`
- Create: `src/backend/tests/Unit/Modules/Workflow/DepartmentHeadResolverTest.php`
- Create: `src/backend/tests/Feature/Modules/Workflow/WorkflowDelegationApiTest.php`
- Create: `src/backend/tests/Feature/Modules/Workflow/WorkflowEngineRoutingTest.php`
- Create: `src/backend/tests/Feature/Modules/Leave/LeaveWorkflowIntegrationTest.php`

---

### Task 1: Add failing tests for condition evaluation

**Files:**
- Create: `src/backend/tests/Unit/Modules/Workflow/ConditionEvaluatorTest.php`
- Create later: `src/backend/app/Modules/Workflow/Application/Services/ConditionEvaluator.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Unit\Modules\Workflow;

use App\Modules\Workflow\Application\Services\ConditionEvaluator;
use PHPUnit\Framework\TestCase;

class ConditionEvaluatorTest extends TestCase
{
    public function test_null_condition_returns_true(): void
    {
        $evaluator = new ConditionEvaluator();
        self::assertTrue($evaluator->evaluate(null, []));
    }

    public function test_and_condition_with_gte_and_in(): void
    {
        $evaluator = new ConditionEvaluator();
        $condition = [
            'op' => 'and',
            'conditions' => [
                ['field' => 'duration_days', 'op' => 'gte', 'value' => 3],
                ['field' => 'leave_type_code', 'op' => 'in', 'value' => ['annual', 'sick']],
            ],
        ];

        $context = ['duration_days' => 5, 'leave_type_code' => 'annual'];

        self::assertTrue($evaluator->evaluate($condition, $context));
    }

    public function test_missing_field_returns_false_for_comparison(): void
    {
        $evaluator = new ConditionEvaluator();
        $condition = ['field' => 'manager_id', 'op' => 'eq', 'value' => 'u-1'];
        self::assertFalse($evaluator->evaluate($condition, []));
    }

    public function test_not_condition_negates_inner_result(): void
    {
        $evaluator = new ConditionEvaluator();
        $condition = [
            'op' => 'not',
            'condition' => ['field' => 'duration_days', 'op' => 'lt', 'value' => 3],
        ];

        self::assertTrue($evaluator->evaluate($condition, ['duration_days' => 5]));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker compose exec -T app php artisan test --compact src/backend/tests/Unit/Modules/Workflow/ConditionEvaluatorTest.php`
Expected: FAIL with `Class "App\Modules\Workflow\Application\Services\ConditionEvaluator" not found`

- [ ] **Step 3: Write minimal implementation**

```php
<?php

namespace App\Modules\Workflow\Application\Services;

use App\Modules\Workflow\Domain\Exceptions\WorkflowConditionEvaluationException;

final class ConditionEvaluator
{
    public function evaluate(?array $condition, array $context): bool
    {
        if ($condition === null) {
            return true;
        }

        if (isset($condition['op']) && in_array($condition['op'], ['and', 'or'], true)) {
            $conditions = $condition['conditions'] ?? [];
            if ($condition['op'] === 'and') {
                foreach ($conditions as $item) {
                    if (! $this->evaluate($item, $context)) {
                        return false;
                    }
                }
                return true;
            }

            foreach ($conditions as $item) {
                if ($this->evaluate($item, $context)) {
                    return true;
                }
            }
            return false;
        }

        if (($condition['op'] ?? null) === 'not') {
            return ! $this->evaluate($condition['condition'] ?? null, $context);
        }

        $field = $condition['field'] ?? null;
        $op = $this->normalizeOp($condition['op'] ?? null);
        $value = $condition['value'] ?? null;

        if ($field === null || $op === null) {
            throw new WorkflowConditionEvaluationException('Malformed workflow condition');
        }

        $exists = array_key_exists($field, $context);
        $actual = $context[$field] ?? null;

        return match ($op) {
            'exists' => $exists,
            'eq' => $exists && $actual === $value,
            'neq' => $exists && $actual !== $value,
            'gt' => $exists && $actual > $value,
            'gte' => $exists && $actual >= $value,
            'lt' => $exists && $actual < $value,
            'lte' => $exists && $actual <= $value,
            'in' => $exists && in_array($actual, (array) $value, true),
            'nin' => $exists && ! in_array($actual, (array) $value, true),
            default => throw new WorkflowConditionEvaluationException('Unsupported workflow condition operator'),
        };
    }

    private function normalizeOp(?string $op): ?string
    {
        return match ($op) {
            '=', 'eq' => 'eq',
            '!=', 'neq' => 'neq',
            '>', 'gt' => 'gt',
            '>=', 'gte' => 'gte',
            '<', 'lt' => 'lt',
            '<=', 'lte' => 'lte',
            'in' => 'in',
            'nin' => 'nin',
            'exists' => 'exists',
            default => null,
        };
    }
}
```

- [ ] **Step 4: Add exception class**

```php
<?php

namespace App\Modules\Workflow\Domain\Exceptions;

use RuntimeException;

final class WorkflowConditionEvaluationException extends RuntimeException
{
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `docker compose exec -T app php artisan test --compact src/backend/tests/Unit/Modules/Workflow/ConditionEvaluatorTest.php`
Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add src/backend/tests/Unit/Modules/Workflow/ConditionEvaluatorTest.php src/backend/app/Modules/Workflow/Application/Services/ConditionEvaluator.php src/backend/app/Modules/Workflow/Domain/Exceptions/WorkflowConditionEvaluationException.php
git commit -m "test: add workflow condition evaluator"
```

### Task 2: Add failing tests for delegation resolution

**Files:**
- Create: `src/backend/tests/Unit/Modules/Workflow/DelegationResolverTest.php`
- Create later: `src/backend/app/Modules/Workflow/Application/Services/DelegationResolver.php`
- Create later: `src/backend/app/Modules/Workflow/Application/Services/DelegationResult.php`
- Create later: `src/backend/app/Modules/Workflow/Domain/Aggregates/WorkflowDelegation/WorkflowDelegation.php`
- Create later: `src/backend/app/Modules/Workflow/Domain/Aggregates/WorkflowDelegation/WorkflowDelegationId.php`

- [ ] **Step 1: Write the failing test**

```php
<?php

namespace Tests\Unit\Modules\Workflow;

use App\Modules\Workflow\Application\Services\DelegationResolver;
use App\Modules\Workflow\Domain\Aggregates\WorkflowDelegation\WorkflowDelegation;
use App\Modules\Workflow\Domain\Aggregates\WorkflowDelegation\WorkflowDelegationId;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class DelegationResolverTest extends TestCase
{
    public function test_active_delegation_replaces_approver_and_preserves_map(): void
    {
        $resolver = new DelegationResolver();
        $delegations = [
            new WorkflowDelegation(
                WorkflowDelegationId::new(),
                'user-a',
                'user-b',
                'workflow_approver',
                CarbonImmutable::parse('2026-07-04 08:00:00'),
                CarbonImmutable::parse('2026-07-05 18:00:00'),
                true,
            ),
        ];

        $result = $resolver->resolve(['user-a', 'user-c'], $delegations, CarbonImmutable::parse('2026-07-04 12:00:00'));

        self::assertSame(['user-b', 'user-c'], $result->effectiveApproverIds);
        self::assertSame(['user-a' => 'user-b'], $result->delegationMap);
    }

    public function test_expired_delegation_is_ignored(): void
    {
        $resolver = new DelegationResolver();
        $delegations = [
            new WorkflowDelegation(
                WorkflowDelegationId::new(),
                'user-a',
                'user-b',
                'workflow_approver',
                CarbonImmutable::parse('2026-07-01 08:00:00'),
                CarbonImmutable::parse('2026-07-02 18:00:00'),
                true,
            ),
        ];

        $result = $resolver->resolve(['user-a'], $delegations, CarbonImmutable::parse('2026-07-04 12:00:00'));

        self::assertSame(['user-a'], $result->effectiveApproverIds);
        self::assertSame([], $result->delegationMap);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker compose exec -T app php artisan test --compact src/backend/tests/Unit/Modules/Workflow/DelegationResolverTest.php`
Expected: FAIL because classes are missing

- [ ] **Step 3: Write minimal result object**

```php
<?php

namespace App\Modules\Workflow\Application\Services;

final class DelegationResult
{
    public function __construct(
        public array $effectiveApproverIds,
        public array $delegationMap,
    ) {}
}
```

- [ ] **Step 4: Write minimal aggregate classes**

```php
<?php

namespace App\Modules\Workflow\Domain\Aggregates\WorkflowDelegation;

use Carbon\CarbonImmutable;
use Ramsey\Uuid\Uuid;

final class WorkflowDelegation
{
    public function __construct(
        private WorkflowDelegationId $id,
        private string $delegatorId,
        private string $delegateId,
        private ?string $roleType,
        private CarbonImmutable $startAt,
        private CarbonImmutable $endAt,
        private bool $active,
    ) {}

    public function delegatorId(): string { return $this->delegatorId; }
    public function delegateId(): string { return $this->delegateId; }
    public function roleType(): ?string { return $this->roleType; }
    public function startAt(): CarbonImmutable { return $this->startAt; }
    public function endAt(): CarbonImmutable { return $this->endAt; }
    public function active(): bool { return $this->active; }
    public function revoke(): void { $this->active = false; }
    public function isEffectiveAt(CarbonImmutable $at): bool
    {
        return $this->active && $at >= $this->startAt && $at <= $this->endAt;
    }
}
```

```php
<?php

namespace App\Modules\Workflow\Domain\Aggregates\WorkflowDelegation;

use Ramsey\Uuid\Uuid;

final class WorkflowDelegationId
{
    public function __construct(private string $value) {}
    public static function new(): self { return new self((string) Uuid::uuid4()); }
    public function value(): string { return $this->value; }
}
```

- [ ] **Step 5: Write minimal resolver**

```php
<?php

namespace App\Modules\Workflow\Application\Services;

use App\Modules\Workflow\Domain\Aggregates\WorkflowDelegation\WorkflowDelegation;
use Carbon\CarbonImmutable;

final class DelegationResolver
{
    /** @param string[] $approverIds @param WorkflowDelegation[] $delegations */
    public function resolve(array $approverIds, array $delegations, CarbonImmutable $at): DelegationResult
    {
        $effective = [];
        $map = [];

        foreach ($approverIds as $approverId) {
            $active = null;
            foreach ($delegations as $delegation) {
                if ($delegation->delegatorId() === $approverId && $delegation->isEffectiveAt($at)) {
                    $active = $delegation;
                    break;
                }
            }

            if ($active !== null && $active->delegateId() !== $approverId) {
                $effective[] = $active->delegateId();
                $map[$approverId] = $active->delegateId();
            } else {
                $effective[] = $approverId;
            }
        }

        return new DelegationResult($effective, $map);
    }
}
```

- [ ] **Step 6: Run test to verify it passes**

Run: `docker compose exec -T app php artisan test --compact src/backend/tests/Unit/Modules/Workflow/DelegationResolverTest.php`
Expected: PASS

- [ ] **Step 7: Commit**

```bash
git add src/backend/tests/Unit/Modules/Workflow/DelegationResolverTest.php src/backend/app/Modules/Workflow/Application/Services/DelegationResolver.php src/backend/app/Modules/Workflow/Application/Services/DelegationResult.php src/backend/app/Modules/Workflow/Domain/Aggregates/WorkflowDelegation/WorkflowDelegation.php src/backend/app/Modules/Workflow/Domain/Aggregates/WorkflowDelegation/WorkflowDelegationId.php
git commit -m "test: add workflow delegation resolver"
```

### Task 3: Add failing tests for resolver registry and built-in resolvers

**Files:**
- Create: `src/backend/tests/Unit/Modules/Workflow/ResolverRegistryTest.php`
- Create: `src/backend/tests/Unit/Modules/Workflow/SpecificUserResolverTest.php`
- Create later: `src/backend/app/Modules/Workflow/Application/Contracts/AssigneeResolver.php`
- Create later: `src/backend/app/Modules/Workflow/Application/Services/ResolverRegistry.php`
- Create later: `src/backend/app/Modules/Workflow/Application/Resolvers/SpecificUserResolver.php`

- [ ] **Step 1: Write failing tests**

```php
<?php

namespace Tests\Unit\Modules\Workflow;

use App\Modules\Workflow\Application\Resolvers\SpecificUserResolver;
use App\Modules\Workflow\Application\Services\ResolverRegistry;
use App\Modules\Workflow\Domain\Exceptions\WorkflowResolverNotFoundException;
use PHPUnit\Framework\TestCase;

class ResolverRegistryTest extends TestCase
{
    public function test_registry_returns_registered_resolver(): void
    {
        $registry = new ResolverRegistry();
        $resolver = new SpecificUserResolver();
        $registry->register($resolver);

        self::assertSame($resolver, $registry->get('specific_user'));
    }

    public function test_registry_throws_for_unknown_resolver(): void
    {
        $registry = new ResolverRegistry();

        $this->expectException(WorkflowResolverNotFoundException::class);
        $registry->get('direct_manager');
    }
}
```

```php
<?php

namespace Tests\Unit\Modules\Workflow;

use App\Modules\Workflow\Application\Resolvers\SpecificUserResolver;
use PHPUnit\Framework\TestCase;

class SpecificUserResolverTest extends TestCase
{
    public function test_resolve_returns_configured_user_id(): void
    {
        $resolver = new SpecificUserResolver();

        self::assertSame(['user-1'], $resolver->resolve(['user_id' => 'user-1'], []));
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `docker compose exec -T app php artisan test --compact src/backend/tests/Unit/Modules/Workflow/ResolverRegistryTest.php src/backend/tests/Unit/Modules/Workflow/SpecificUserResolverTest.php`
Expected: FAIL because registry/resolver classes are missing

- [ ] **Step 3: Add exception and contract**

```php
<?php

namespace App\Modules\Workflow\Domain\Exceptions;

use RuntimeException;

final class WorkflowResolverNotFoundException extends RuntimeException
{
}
```

```php
<?php

namespace App\Modules\Workflow\Application\Contracts;

interface AssigneeResolver
{
    public function key(): string;

    /** @return string[] */
    public function resolve(array $config, array $context): array;
}
```

- [ ] **Step 4: Add registry and specific resolver**

```php
<?php

namespace App\Modules\Workflow\Application\Services;

use App\Modules\Workflow\Application\Contracts\AssigneeResolver;
use App\Modules\Workflow\Domain\Exceptions\WorkflowResolverNotFoundException;

final class ResolverRegistry
{
    /** @var array<string, AssigneeResolver> */
    private array $resolvers = [];

    public function register(AssigneeResolver $resolver): void
    {
        $this->resolvers[$resolver->key()] = $resolver;
    }

    public function get(string $key): AssigneeResolver
    {
        if (! isset($this->resolvers[$key])) {
            throw new WorkflowResolverNotFoundException("Không tìm thấy assignee resolver: {$key}");
        }

        return $this->resolvers[$key];
    }
}
```

```php
<?php

namespace App\Modules\Workflow\Application\Resolvers;

use App\Modules\Workflow\Application\Contracts\AssigneeResolver;

final class SpecificUserResolver implements AssigneeResolver
{
    public function key(): string
    {
        return 'specific_user';
    }

    public function resolve(array $config, array $context): array
    {
        return [$config['user_id']];
    }
}
```

- [ ] **Step 5: Run tests to verify they pass**

Run: `docker compose exec -T app php artisan test --compact src/backend/tests/Unit/Modules/Workflow/ResolverRegistryTest.php src/backend/tests/Unit/Modules/Workflow/SpecificUserResolverTest.php`
Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add src/backend/tests/Unit/Modules/Workflow/ResolverRegistryTest.php src/backend/tests/Unit/Modules/Workflow/SpecificUserResolverTest.php src/backend/app/Modules/Workflow/Application/Contracts/AssigneeResolver.php src/backend/app/Modules/Workflow/Application/Services/ResolverRegistry.php src/backend/app/Modules/Workflow/Application/Resolvers/SpecificUserResolver.php src/backend/app/Modules/Workflow/Domain/Exceptions/WorkflowResolverNotFoundException.php
git commit -m "test: add workflow resolver registry"
```

### Task 4: Add migrations and Eloquent models for workflow engine fields and delegations

**Files:**
- Create: `src/backend/database/migrations/2026_07_04_000001_add_engine_fields_to_workflow_template_steps.php`
- Create: `src/backend/database/migrations/2026_07_04_000002_add_context_to_workflow_requests.php`
- Create: `src/backend/database/migrations/2026_07_04_000003_add_resolution_metadata_to_workflow_request_actions.php`
- Create: `src/backend/database/migrations/2026_07_04_000004_create_workflow_delegations_table.php`
- Create: `src/backend/database/migrations/2026_07_04_000005_add_workflow_template_code_to_leave_types.php`
- Create: `src/backend/app/Modules/Workflow/Infrastructure/Persistence/Eloquent/WorkflowDelegationModel.php`
- Modify: `src/backend/app/Modules/Workflow/Infrastructure/Persistence/Eloquent/WorkflowTemplateStepModel.php`
- Modify: `src/backend/app/Modules/Workflow/Infrastructure/Persistence/Eloquent/WorkflowRequestModel.php`
- Modify: `src/backend/app/Modules/Workflow/Infrastructure/Persistence/Eloquent/WorkflowRequestActionModel.php`
- Modify: `src/backend/app/Modules/Leave/Infrastructure/Persistence/Eloquent/LeaveTypeModel.php`

- [ ] **Step 1: Write a migration smoke test first**

```php
<?php

namespace Tests\Feature\Modules\Workflow;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class WorkflowSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_workflow_engine_columns_and_tables_exist(): void
    {
        self::assertTrue(Schema::hasColumn('workflow_template_steps', 'resolver_type'));
        self::assertTrue(Schema::hasColumn('workflow_template_steps', 'resolver_config'));
        self::assertTrue(Schema::hasColumn('workflow_requests', 'context'));
        self::assertTrue(Schema::hasColumn('workflow_request_actions', 'resolved_approvers'));
        self::assertTrue(Schema::hasColumn('workflow_request_actions', 'delegation_map'));
        self::assertTrue(Schema::hasTable('workflow_delegations'));
        self::assertTrue(Schema::hasColumn('leave_types', 'workflow_template_code'));
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker compose exec -T app php artisan test --compact src/backend/tests/Feature/Modules/Workflow/WorkflowSchemaTest.php`
Expected: FAIL on missing columns/table

- [ ] **Step 3: Add migrations**

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
            $table->string('resolver_type', 40)->nullable()->after('assignee_id');
            $table->jsonb('resolver_config')->default('{}')->after('resolver_type');
        });
    }

    public function down(): void
    {
        Schema::table('workflow_template_steps', function (Blueprint $table) {
            $table->dropColumn(['resolver_type', 'resolver_config']);
        });
    }
};
```

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
            $table->jsonb('context')->nullable()->after('submitted_by');
        });
    }

    public function down(): void
    {
        Schema::table('workflow_requests', function (Blueprint $table) {
            $table->dropColumn('context');
        });
    }
};
```

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
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflow_delegations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('delegator_id');
            $table->uuid('delegate_id');
            $table->string('role_type', 30)->nullable();
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->boolean('active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->index(['delegator_id', 'active']);
            $table->index(['start_at', 'end_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflow_delegations');
    }
};
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->string('workflow_template_code', 80)->nullable()->after('balance_tracked');
        });
    }

    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn('workflow_template_code');
        });
    }
};
```

- [ ] **Step 4: Update Eloquent models**

```php
<?php

namespace App\Modules\Workflow\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

class WorkflowDelegationModel extends Model
{
    protected $table = 'workflow_delegations';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'delegator_id', 'delegate_id', 'role_type', 'start_at', 'end_at', 'active', 'created_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'active' => 'boolean',
    ];
}
```

```php
// WorkflowTemplateStepModel add to $fillable and $casts
protected $fillable = ['id', 'workflow_template_id', 'step_order', 'name', 'assignee_type', 'assignee_id', 'resolver_type', 'resolver_config', 'condition'];
protected $casts = ['step_order' => 'integer', 'resolver_config' => 'array', 'condition' => 'array'];
```

```php
// WorkflowRequestModel add to $fillable and $casts
protected $fillable = ['id', 'workflow_template_id', 'subject_type', 'subject_id', 'status', 'current_step', 'submitted_by', 'context'];
protected $casts = ['context' => 'array'];
```

```php
// WorkflowRequestActionModel add to $fillable and $casts
protected $fillable = ['id', 'workflow_request_id', 'step_order', 'action', 'actor_id', 'comment', 'metadata', 'resolved_approvers', 'delegation_map', 'created_at'];
protected $casts = ['metadata' => 'array', 'resolved_approvers' => 'array', 'delegation_map' => 'array', 'created_at' => 'datetime'];
```

```php
// LeaveTypeModel add workflow_template_code to fillable
protected $fillable = [/* existing fields */, 'workflow_template_code'];
```

- [ ] **Step 5: Run test to verify it passes**

Run: `docker compose exec -T app php artisan test --compact src/backend/tests/Feature/Modules/Workflow/WorkflowSchemaTest.php`
Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add src/backend/tests/Feature/Modules/Workflow/WorkflowSchemaTest.php src/backend/database/migrations/2026_07_04_000001_add_engine_fields_to_workflow_template_steps.php src/backend/database/migrations/2026_07_04_000002_add_context_to_workflow_requests.php src/backend/database/migrations/2026_07_04_000003_add_resolution_metadata_to_workflow_request_actions.php src/backend/database/migrations/2026_07_04_000004_create_workflow_delegations_table.php src/backend/database/migrations/2026_07_04_000005_add_workflow_template_code_to_leave_types.php src/backend/app/Modules/Workflow/Infrastructure/Persistence/Eloquent/WorkflowDelegationModel.php src/backend/app/Modules/Workflow/Infrastructure/Persistence/Eloquent/WorkflowTemplateStepModel.php src/backend/app/Modules/Workflow/Infrastructure/Persistence/Eloquent/WorkflowRequestModel.php src/backend/app/Modules/Workflow/Infrastructure/Persistence/Eloquent/WorkflowRequestActionModel.php src/backend/app/Modules/Leave/Infrastructure/Persistence/Eloquent/LeaveTypeModel.php
git commit -m "feat: add workflow engine schema"
```

### Task 5: Extend domain models and repositories for new workflow metadata

**Files:**
- Modify: `src/backend/app/Modules/Workflow/Domain/Aggregates/WorkflowTemplate/WorkflowStep.php`
- Modify: `src/backend/app/Modules/Workflow/Domain/Aggregates/WorkflowRequest/WorkflowAction.php`
- Modify: `src/backend/app/Modules/Workflow/Domain/Aggregates/WorkflowRequest/WorkflowRequest.php`
- Modify: `src/backend/app/Modules/Workflow/Domain/Repositories/WorkflowRequestRepositoryInterface.php`
- Create: `src/backend/app/Modules/Workflow/Domain/Repositories/WorkflowDelegationRepositoryInterface.php`
- Create: `src/backend/app/Modules/Workflow/Infrastructure/Persistence/Repositories/EloquentWorkflowDelegationRepository.php`
- Modify: `src/backend/app/Modules/Workflow/Infrastructure/Persistence/Repositories/EloquentWorkflowRequestRepository.php`
- Modify: `src/backend/app/Modules/Workflow/Infrastructure/Persistence/Repositories/EloquentWorkflowTemplateRepository.php`

- [ ] **Step 1: Write failing unit test for workflow request metadata**

```php
<?php

namespace Tests\Unit\Modules\Workflow;

use App\Modules\Workflow\Domain\Aggregates\WorkflowRequest\WorkflowRequest;
use App\Modules\Workflow\Domain\Aggregates\WorkflowRequest\WorkflowRequestId;
use App\Modules\Workflow\Domain\Aggregates\WorkflowTemplate\WorkflowTemplateId;
use PHPUnit\Framework\TestCase;

class WorkflowRequestMetadataTest extends TestCase
{
    public function test_request_can_store_context_and_resolved_approvers(): void
    {
        $request = new WorkflowRequest(
            WorkflowRequestId::new(),
            WorkflowTemplateId::new(),
            'leave_request',
            'subject-1',
            'user-1',
            null,
            null,
            [],
            ['duration_days' => 5],
        );

        $request->moveToStep(2, ['manager-1'], ['director-1' => 'manager-1']);

        self::assertSame(['duration_days' => 5], $request->context());
        self::assertSame(2, $request->currentStep());
        self::assertSame(['manager-1'], $request->resolvedApprovers());
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker compose exec -T app php artisan test --compact src/backend/tests/Unit/Modules/Workflow/WorkflowRequestMetadataTest.php`
Expected: FAIL on missing constructor arg / methods

- [ ] **Step 3: Extend domain objects minimally**

```php
// WorkflowStep new fields
public function __construct(
    private WorkflowStepId $id,
    private int $stepOrder,
    private string $name,
    private AssigneeType $assigneeType,
    private ?string $assigneeId = null,
    private ?array $condition = null,
    private ?string $resolverType = null,
    private array $resolverConfig = [],
) {}

public function resolverType(): ?string { return $this->resolverType; }
public function resolverConfig(): array { return $this->resolverConfig; }
```

```php
// WorkflowAction new metadata fields
public function __construct(
    private WorkflowActionId $id,
    private WorkflowRequestId $workflowRequestId,
    private int $stepOrder,
    private WorkflowActionType $action,
    private string $actorId,
    private ?string $comment = null,
    private array $metadata = [],
    private array $resolvedApprovers = [],
    private array $delegationMap = [],
    private ?CarbonImmutable $createdAt = null,
) {
    $this->createdAt = $createdAt ?? CarbonImmutable::now();
}

public function resolvedApprovers(): array { return $this->resolvedApprovers; }
public function delegationMap(): array { return $this->delegationMap; }
```

```php
// WorkflowRequest new context and step move helpers
private array $context;
private array $resolvedApprovers = [];

public function __construct(
    private WorkflowRequestId $id,
    private WorkflowTemplateId $workflowTemplateId,
    private string $subjectType,
    private string $subjectId,
    private string $submittedBy,
    ?RequestStatus $status = null,
    ?int $currentStep = null,
    array $actions = [],
    array $context = [],
    array $resolvedApprovers = [],
) {
    $this->status = $status ?? RequestStatus::PENDING;
    $this->currentStep = $currentStep;
    $this->actions = $actions;
    $this->context = $context;
    $this->resolvedApprovers = $resolvedApprovers;
}

public function context(): array { return $this->context; }
public function resolvedApprovers(): array { return $this->resolvedApprovers; }
public function setContext(array $context): void { $this->context = $context; }
public function moveToStep(int $stepOrder, array $resolvedApprovers, array $delegationMap): void
{
    $this->status = RequestStatus::IN_REVIEW;
    $this->currentStep = $stepOrder;
    $this->resolvedApprovers = $resolvedApprovers;
    $this->actions[] = new WorkflowAction(
        WorkflowActionId::new(),
        $this->id,
        $stepOrder,
        WorkflowActionType::APPROVE,
        $this->submittedBy,
        'Step routed',
        [],
        $resolvedApprovers,
        $delegationMap,
    );
}

public function markApproved(): void
{
    $this->status = RequestStatus::APPROVED;
    $this->currentStep = null;
    $this->resolvedApprovers = [];
}
```

- [ ] **Step 4: Extend repositories**

```php
<?php

namespace App\Modules\Workflow\Domain\Repositories;

use App\Modules\Workflow\Domain\Aggregates\WorkflowDelegation\WorkflowDelegation;

interface WorkflowDelegationRepositoryInterface
{
    /** @return WorkflowDelegation[] */
    public function findActiveByDelegatorIds(array $delegatorIds): array;
    public function findById(string $id): ?WorkflowDelegation;
    public function save(WorkflowDelegation $delegation): void;
}
```

Update the Eloquent repositories to map `resolver_type`, `resolver_config`, `context`, `resolved_approvers`, `delegation_map`, and create the new `EloquentWorkflowDelegationRepository` with `findActiveByDelegatorIds()`.

- [ ] **Step 5: Run test to verify it passes**

Run: `docker compose exec -T app php artisan test --compact src/backend/tests/Unit/Modules/Workflow/WorkflowRequestMetadataTest.php`
Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add src/backend/tests/Unit/Modules/Workflow/WorkflowRequestMetadataTest.php src/backend/app/Modules/Workflow/Domain/Aggregates/WorkflowTemplate/WorkflowStep.php src/backend/app/Modules/Workflow/Domain/Aggregates/WorkflowRequest/WorkflowAction.php src/backend/app/Modules/Workflow/Domain/Aggregates/WorkflowRequest/WorkflowRequest.php src/backend/app/Modules/Workflow/Domain/Repositories/WorkflowRequestRepositoryInterface.php src/backend/app/Modules/Workflow/Domain/Repositories/WorkflowDelegationRepositoryInterface.php src/backend/app/Modules/Workflow/Infrastructure/Persistence/Repositories/EloquentWorkflowDelegationRepository.php src/backend/app/Modules/Workflow/Infrastructure/Persistence/Repositories/EloquentWorkflowRequestRepository.php src/backend/app/Modules/Workflow/Infrastructure/Persistence/Repositories/EloquentWorkflowTemplateRepository.php
git commit -m "feat: extend workflow domain for engine metadata"
```

### Task 6: Implement subject provider registry and Leave subject provider

**Files:**
- Create: `src/backend/app/Modules/Workflow/Application/Contracts/SubjectDataProvider.php`
- Create: `src/backend/app/Modules/Workflow/Application/Services/SubjectDataProviderRegistry.php`
- Create: `src/backend/app/Modules/Workflow/Domain/Exceptions/WorkflowSubjectProviderNotFoundException.php`
- Create: `src/backend/app/Modules/Leave/Application/Workflow/LeaveRequestSubjectProvider.php`
- Test: `src/backend/tests/Unit/Modules/Workflow/LeaveRequestSubjectProviderTest.php`

- [ ] **Step 1: Write failing provider test**

```php
<?php

namespace Tests\Unit\Modules\Workflow;

use App\Modules\Leave\Application\Workflow\LeaveRequestSubjectProvider;
use App\Modules\Leave\Infrastructure\Persistence\Eloquent\LeaveRequestModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveRequestSubjectProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_provider_builds_context_for_leave_request(): void
    {
        $request = LeaveRequestModel::factory()->create([
            'status' => 'pending',
            'start_at' => '2026-07-05',
            'end_at' => '2026-07-09',
            'duration_minutes' => 2400,
        ]);

        $provider = app(LeaveRequestSubjectProvider::class);
        $context = $provider->fetchContext($request->id);

        self::assertSame('leave_request', $provider->subjectType());
        self::assertSame(5, $context['duration_days']);
        self::assertSame($request->id, $context['subject_id']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker compose exec -T app php artisan test --compact src/backend/tests/Unit/Modules/Workflow/LeaveRequestSubjectProviderTest.php`
Expected: FAIL because provider contract/class are missing

- [ ] **Step 3: Create provider contract, registry, exception**

```php
<?php

namespace App\Modules\Workflow\Application\Contracts;

interface SubjectDataProvider
{
    public function subjectType(): string;
    public function fetchContext(string $subjectId): array;
}
```

```php
<?php

namespace App\Modules\Workflow\Application\Services;

use App\Modules\Workflow\Application\Contracts\SubjectDataProvider;
use App\Modules\Workflow\Domain\Exceptions\WorkflowSubjectProviderNotFoundException;

final class SubjectDataProviderRegistry
{
    /** @var array<string, SubjectDataProvider> */
    private array $providers = [];

    public function register(SubjectDataProvider $provider): void
    {
        $this->providers[$provider->subjectType()] = $provider;
    }

    public function get(string $subjectType): SubjectDataProvider
    {
        if (! isset($this->providers[$subjectType])) {
            throw new WorkflowSubjectProviderNotFoundException("Không tìm thấy subject provider cho loại {$subjectType}");
        }

        return $this->providers[$subjectType];
    }
}
```

```php
<?php

namespace App\Modules\Workflow\Domain\Exceptions;

use RuntimeException;

final class WorkflowSubjectProviderNotFoundException extends RuntimeException
{
}
```

- [ ] **Step 4: Create Leave subject provider**

```php
<?php

namespace App\Modules\Leave\Application\Workflow;

use App\Modules\Leave\Infrastructure\Persistence\Eloquent\LeaveRequestModel;
use App\Modules\Workflow\Application\Contracts\SubjectDataProvider;
use Carbon\CarbonImmutable;

final class LeaveRequestSubjectProvider implements SubjectDataProvider
{
    public function subjectType(): string
    {
        return 'leave_request';
    }

    public function fetchContext(string $subjectId): array
    {
        $request = LeaveRequestModel::query()->findOrFail($subjectId);
        $start = CarbonImmutable::parse($request->start_at);
        $end = CarbonImmutable::parse($request->end_at);
        $days = $start->diffInDays($end) + 1;

        return [
            'subject_type' => 'leave_request',
            'subject_id' => $request->id,
            'employee_id' => $request->employee_id,
            'manager_id' => $request->manager_id,
            'department_id' => $request->department_id,
            'department_head_user_id' => $request->department_head_user_id,
            'leave_type_id' => $request->leave_type_id,
            'leave_type_code' => $request->leave_type_code,
            'duration_days' => $days,
            'duration_minutes' => $request->duration_minutes,
            'start_at' => $request->start_at,
            'end_at' => $request->end_at,
        ];
    }
}
```

- [ ] **Step 5: Run test to verify it passes**

Run: `docker compose exec -T app php artisan test --compact src/backend/tests/Unit/Modules/Workflow/LeaveRequestSubjectProviderTest.php`
Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add src/backend/tests/Unit/Modules/Workflow/LeaveRequestSubjectProviderTest.php src/backend/app/Modules/Workflow/Application/Contracts/SubjectDataProvider.php src/backend/app/Modules/Workflow/Application/Services/SubjectDataProviderRegistry.php src/backend/app/Modules/Workflow/Domain/Exceptions/WorkflowSubjectProviderNotFoundException.php src/backend/app/Modules/Leave/Application/Workflow/LeaveRequestSubjectProvider.php
git commit -m "feat: add workflow subject provider registry"
```

### Task 7: Implement built-in manager and department resolvers

**Files:**
- Create: `src/backend/app/Modules/Workflow/Application/Resolvers/DirectManagerResolver.php`
- Create: `src/backend/app/Modules/Workflow/Application/Resolvers/DepartmentHeadResolver.php`
- Create: `src/backend/app/Modules/Workflow/Application/Resolvers/RoleResolver.php`
- Create: `src/backend/app/Modules/Workflow/Application/Resolvers/RoleInDepartmentResolver.php`
- Create: `src/backend/tests/Unit/Modules/Workflow/DirectManagerResolverTest.php`
- Create: `src/backend/tests/Unit/Modules/Workflow/DepartmentHeadResolverTest.php`

- [ ] **Step 1: Write failing tests**

```php
<?php

namespace Tests\Unit\Modules\Workflow;

use App\Modules\Workflow\Application\Resolvers\DirectManagerResolver;
use PHPUnit\Framework\TestCase;

class DirectManagerResolverTest extends TestCase
{
    public function test_resolve_manager_id_from_context(): void
    {
        $resolver = new DirectManagerResolver();
        self::assertSame(['manager-1'], $resolver->resolve([], ['manager_id' => 'manager-1']));
    }
}
```

```php
<?php

namespace Tests\Unit\Modules\Workflow;

use App\Modules\Workflow\Application\Resolvers\DepartmentHeadResolver;
use PHPUnit\Framework\TestCase;

class DepartmentHeadResolverTest extends TestCase
{
    public function test_resolve_department_head_id_from_context(): void
    {
        $resolver = new DepartmentHeadResolver();
        self::assertSame(['head-1'], $resolver->resolve([], ['department_head_user_id' => 'head-1']));
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `docker compose exec -T app php artisan test --compact src/backend/tests/Unit/Modules/Workflow/DirectManagerResolverTest.php src/backend/tests/Unit/Modules/Workflow/DepartmentHeadResolverTest.php`
Expected: FAIL because resolver classes are missing

- [ ] **Step 3: Implement direct manager and department head resolvers**

```php
<?php

namespace App\Modules\Workflow\Application\Resolvers;

use App\Modules\Workflow\Application\Contracts\AssigneeResolver;

final class DirectManagerResolver implements AssigneeResolver
{
    public function key(): string
    {
        return 'direct_manager';
    }

    public function resolve(array $config, array $context): array
    {
        return isset($context['manager_id']) ? [$context['manager_id']] : [];
    }
}
```

```php
<?php

namespace App\Modules\Workflow\Application\Resolvers;

use App\Modules\Workflow\Application\Contracts\AssigneeResolver;

final class DepartmentHeadResolver implements AssigneeResolver
{
    public function key(): string
    {
        return 'department_head';
    }

    public function resolve(array $config, array $context): array
    {
        return isset($context['department_head_user_id']) ? [$context['department_head_user_id']] : [];
    }
}
```

- [ ] **Step 4: Implement minimal role-based resolvers with TODO-safe exception for missing adapters**

```php
<?php

namespace App\Modules\Workflow\Application\Resolvers;

use App\Modules\Workflow\Application\Contracts\AssigneeResolver;

final class RoleResolver implements AssigneeResolver
{
    public function __construct(private readonly RoleLookup $lookup) {}

    public function key(): string
    {
        return 'role';
    }

    public function resolve(array $config, array $context): array
    {
        return $this->lookup->usersByRole($config['role_code']);
    }
}
```

```php
<?php

namespace App\Modules\Workflow\Application\Resolvers;

use App\Modules\Workflow\Application\Contracts\AssigneeResolver;

final class RoleInDepartmentResolver implements AssigneeResolver
{
    public function __construct(private readonly RoleLookup $lookup) {}

    public function key(): string
    {
        return 'role_in_department';
    }

    public function resolve(array $config, array $context): array
    {
        $departmentId = $context['department_id'] ?? null;
        return $departmentId ? $this->lookup->usersByRoleInDepartment($config['role_code'], $departmentId) : [];
    }
}
```

Also create the tiny `RoleLookup` contract under `Workflow/Application/Resolvers/RoleLookup.php`.

- [ ] **Step 5: Run tests to verify they pass**

Run: `docker compose exec -T app php artisan test --compact src/backend/tests/Unit/Modules/Workflow/DirectManagerResolverTest.php src/backend/tests/Unit/Modules/Workflow/DepartmentHeadResolverTest.php`
Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add src/backend/tests/Unit/Modules/Workflow/DirectManagerResolverTest.php src/backend/tests/Unit/Modules/Workflow/DepartmentHeadResolverTest.php src/backend/app/Modules/Workflow/Application/Resolvers/DirectManagerResolver.php src/backend/app/Modules/Workflow/Application/Resolvers/DepartmentHeadResolver.php src/backend/app/Modules/Workflow/Application/Resolvers/RoleResolver.php src/backend/app/Modules/Workflow/Application/Resolvers/RoleInDepartmentResolver.php src/backend/app/Modules/Workflow/Application/Resolvers/RoleLookup.php
git commit -m "feat: add built-in workflow resolvers"
```

### Task 8: Implement workflow delegation repository, commands, handlers, API, and permissions

**Files:**
- Create: `src/backend/app/Modules/Workflow/Application/Commands/CreateWorkflowDelegationCommand.php`
- Create: `src/backend/app/Modules/Workflow/Application/Commands/RevokeWorkflowDelegationCommand.php`
- Create: `src/backend/app/Modules/Workflow/Application/CommandHandlers/CreateWorkflowDelegationHandler.php`
- Create: `src/backend/app/Modules/Workflow/Application/CommandHandlers/RevokeWorkflowDelegationHandler.php`
- Create: `src/backend/app/Modules/Workflow/Application/Queries/ListWorkflowDelegationsQuery.php`
- Create: `src/backend/app/Modules/Workflow/Application/QueryHandlers/ListWorkflowDelegationsHandler.php`
- Create: `src/backend/app/Modules/Workflow/Infrastructure/Http/Controllers/WorkflowDelegationController.php`
- Create: `src/backend/app/Modules/Workflow/Infrastructure/Http/Controllers/Actions/ListWorkflowDelegationController.php`
- Create: `src/backend/app/Modules/Workflow/Infrastructure/Http/Controllers/Actions/StoreWorkflowDelegationController.php`
- Create: `src/backend/app/Modules/Workflow/Infrastructure/Http/Controllers/Actions/DeleteWorkflowDelegationController.php`
- Create: `src/backend/app/Modules/Workflow/Infrastructure/Http/Requests/CreateWorkflowDelegationRequest.php`
- Create: `src/backend/app/Modules/Workflow/Infrastructure/Http/Resources/WorkflowDelegationResource.php`
- Modify: `src/backend/app/Modules/Workflow/Routes/api.php`
- Test: `src/backend/tests/Feature/Modules/Workflow/WorkflowDelegationApiTest.php`

- [ ] **Step 1: Write failing API tests**

```php
<?php

namespace Tests\Feature\Modules\Workflow;

use App\Modules\Identity\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WorkflowDelegationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_list_and_revoke_delegation(): void
    {
        $user = UserModel::factory()->create();
        $delegate = UserModel::factory()->create();
        Sanctum::actingAs($user);

        $create = $this->postJson('/api/v1/workflow-delegations', [
            'delegate_id' => $delegate->id,
            'role_type' => 'workflow_approver',
            'start_at' => '2026-07-05 08:00:00',
            'end_at' => '2026-07-06 18:00:00',
        ]);
        $create->assertCreated();

        $list = $this->getJson('/api/v1/workflow-delegations');
        $list->assertOk()->assertJsonPath('data.0.delegate_id', $delegate->id);

        $id = $create->json('data.id');
        $delete = $this->deleteJson("/api/v1/workflow-delegations/{$id}");
        $delete->assertOk()->assertJsonPath('data.active', false);
    }

    public function test_guest_cannot_create_delegation(): void
    {
        $this->postJson('/api/v1/workflow-delegations', [])->assertUnauthorized();
    }
}
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `docker compose exec -T app php artisan test --compact src/backend/tests/Feature/Modules/Workflow/WorkflowDelegationApiTest.php`
Expected: FAIL on missing route/controller

- [ ] **Step 3: Implement commands, handlers, request, resource, controller**

Use these exact class shapes:

```php
final readonly class CreateWorkflowDelegationCommand
{
    public function __construct(
        public string $delegatorId,
        public string $delegateId,
        public ?string $roleType,
        public string $startAt,
        public string $endAt,
        public ?string $createdBy,
    ) {}
}
```

```php
final readonly class RevokeWorkflowDelegationCommand
{
    public function __construct(public string $id, public string $actorId) {}
}
```

```php
final class CreateWorkflowDelegationRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'delegate_id' => 'required|uuid|different:delegator_id',
            'role_type' => 'nullable|string|max:30',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
        ];
    }
}
```

The create handler must reject overlap for the same `delegator_id + role_type` using `WorkflowDelegationConflictException`.

- [ ] **Step 4: Add routes**

```php
Route::get('workflow-delegations', ListWorkflowDelegationController::class);
Route::post('workflow-delegations', StoreWorkflowDelegationController::class);
Route::delete('workflow-delegations/{id}', DeleteWorkflowDelegationController::class);
```

Add `auth:sanctum` and workflow permission middleware matching existing module style.

- [ ] **Step 5: Run tests to verify they pass**

Run: `docker compose exec -T app php artisan test --compact src/backend/tests/Feature/Modules/Workflow/WorkflowDelegationApiTest.php`
Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add src/backend/tests/Feature/Modules/Workflow/WorkflowDelegationApiTest.php src/backend/app/Modules/Workflow/Application/Commands/CreateWorkflowDelegationCommand.php src/backend/app/Modules/Workflow/Application/Commands/RevokeWorkflowDelegationCommand.php src/backend/app/Modules/Workflow/Application/CommandHandlers/CreateWorkflowDelegationHandler.php src/backend/app/Modules/Workflow/Application/CommandHandlers/RevokeWorkflowDelegationHandler.php src/backend/app/Modules/Workflow/Application/Queries/ListWorkflowDelegationsQuery.php src/backend/app/Modules/Workflow/Application/QueryHandlers/ListWorkflowDelegationsHandler.php src/backend/app/Modules/Workflow/Infrastructure/Http/Controllers/WorkflowDelegationController.php src/backend/app/Modules/Workflow/Infrastructure/Http/Controllers/Actions/ListWorkflowDelegationController.php src/backend/app/Modules/Workflow/Infrastructure/Http/Controllers/Actions/StoreWorkflowDelegationController.php src/backend/app/Modules/Workflow/Infrastructure/Http/Controllers/Actions/DeleteWorkflowDelegationController.php src/backend/app/Modules/Workflow/Infrastructure/Http/Requests/CreateWorkflowDelegationRequest.php src/backend/app/Modules/Workflow/Infrastructure/Http/Resources/WorkflowDelegationResource.php src/backend/app/Modules/Workflow/Routes/api.php
git commit -m "feat: add workflow delegation api"
```

### Task 9: Implement WorkflowEngine and wire submit/approve handlers

**Files:**
- Create: `src/backend/app/Modules/Workflow/Application/Services/WorkflowEngine.php`
- Modify: `src/backend/app/Modules/Workflow/Application/CommandHandlers/SubmitWorkflowRequestHandler.php`
- Modify: `src/backend/app/Modules/Workflow/Application/CommandHandlers/ApproveWorkflowStepHandler.php`
- Modify: `src/backend/app/Modules/Workflow/Infrastructure/Http/Requests/CreateWorkflowTemplateRequest.php`
- Modify: `src/backend/app/Modules/Workflow/Infrastructure/Persistence/Repositories/EloquentWorkflowTemplateRepository.php`
- Test: `src/backend/tests/Feature/Modules/Workflow/WorkflowEngineRoutingTest.php`

- [ ] **Step 1: Write failing routing feature test**

```php
<?php

namespace Tests\Feature\Modules\Workflow;

use App\Modules\Identity\Infrastructure\Persistence\Eloquent\UserModel;
use App\Modules\Workflow\Infrastructure\Persistence\Eloquent\WorkflowTemplateModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WorkflowEngineRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_approve_skips_false_condition_and_routes_to_next_valid_step(): void
    {
        $user = UserModel::factory()->create();
        Sanctum::actingAs($user);

        $template = WorkflowTemplateModel::create([
            'id' => '11111111-1111-1111-1111-111111111111',
            'code' => 'LEAVE-APPROVAL',
            'name' => 'Leave approval',
            'description' => null,
            'active' => true,
        ]);

        $this->postJson('/api/v1/workflow-templates', [
            'code' => 'LEAVE-APPROVAL',
            'name' => 'Leave approval',
            'description' => null,
            'steps' => [
                ['step_order' => 1, 'name' => 'Manager', 'resolver_type' => 'direct_manager', 'resolver_config' => [], 'condition' => null],
                ['step_order' => 2, 'name' => 'Director', 'resolver_type' => 'specific_user', 'resolver_config' => ['user_id' => 'director-1'], 'condition' => ['field' => 'duration_days', 'op' => 'gte', 'value' => 10]],
                ['step_order' => 3, 'name' => 'HR', 'resolver_type' => 'specific_user', 'resolver_config' => ['user_id' => 'hr-1'], 'condition' => null],
            ],
        ])->assertCreated();

        $createRequest = $this->postJson('/api/v1/workflow-requests', [
            'workflow_template_id' => $template->id,
            'subject_type' => 'leave_request',
            'subject_id' => 'subject-1',
        ]);
        $createRequest->assertOk();

        $workflowRequestId = $createRequest->json('data.id');

        $approve = $this->postJson("/api/v1/workflow-requests/{$workflowRequestId}/approve", ['comment' => 'ok']);
        $approve->assertNoContent();

        $show = $this->getJson("/api/v1/workflow-requests/{$workflowRequestId}");
        $show->assertOk()->assertJsonPath('data.current_step', 3);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker compose exec -T app php artisan test --compact src/backend/tests/Feature/Modules/Workflow/WorkflowEngineRoutingTest.php`
Expected: FAIL because template payload and engine routing are not implemented

- [ ] **Step 3: Implement `WorkflowEngine`**

```php
<?php

namespace App\Modules\Workflow\Application\Services;

use App\Modules\Workflow\Domain\Aggregates\WorkflowRequest\WorkflowRequest;
use App\Modules\Workflow\Domain\Aggregates\WorkflowTemplate\WorkflowTemplate;
use App\Modules\Workflow\Domain\Repositories\WorkflowDelegationRepositoryInterface;
use Carbon\CarbonImmutable;

final class WorkflowEngine
{
    public function __construct(
        private readonly ConditionEvaluator $conditionEvaluator,
        private readonly ResolverRegistry $resolverRegistry,
        private readonly SubjectDataProviderRegistry $subjectProviderRegistry,
        private readonly WorkflowDelegationRepositoryInterface $delegations,
        private readonly DelegationResolver $delegationResolver,
    ) {}

    public function advanceFromSubmit(WorkflowRequest $request, WorkflowTemplate $template): void
    {
        $provider = $this->subjectProviderRegistry->get($request->subjectType());
        $context = $provider->fetchContext($request->subjectId());
        $request->setContext($context);
        $this->advanceToNextValidStep($request, $template, 0, $context);
    }

    public function advanceAfterApproval(WorkflowRequest $request, WorkflowTemplate $template): void
    {
        $this->advanceToNextValidStep($request, $template, $request->currentStep() ?? 0, $request->context());
    }

    private function advanceToNextValidStep(WorkflowRequest $request, WorkflowTemplate $template, int $currentStep, array $context): void
    {
        $step = $template->nextStepAfter($currentStep);

        while ($step !== null) {
            if ($this->conditionEvaluator->evaluate($step->condition(), $context)) {
                $resolver = $this->resolverRegistry->get($step->resolverType() ?? 'specific_user');
                $approvers = $resolver->resolve($step->resolverConfig(), $context);
                $delegations = $this->delegations->findActiveByDelegatorIds($approvers);
                $delegationResult = $this->delegationResolver->resolve($approvers, $delegations, CarbonImmutable::now());
                $request->moveToStep($step->stepOrder(), $delegationResult->effectiveApproverIds, $delegationResult->delegationMap);
                return;
            }

            $step = $template->nextStepAfter($step->stepOrder());
        }

        $request->markApproved();
    }
}
```

- [ ] **Step 4: Update handlers and request validation**

`SubmitWorkflowRequestHandler` must call `WorkflowEngine::advanceFromSubmit()` after creating the request.

`ApproveWorkflowStepHandler` must call `WorkflowEngine::advanceAfterApproval()` after `approveStep()`.

`CreateWorkflowTemplateRequest` must validate new step payload:

```php
'steps.*.resolver_type' => 'required|string|max:40',
'steps.*.resolver_config' => 'nullable|array',
'steps.*.condition' => 'nullable|array',
```

The old `assignee_type` and `assignee_id` fields should remain nullable for backward compatibility.

- [ ] **Step 5: Run test to verify it passes**

Run: `docker compose exec -T app php artisan test --compact src/backend/tests/Feature/Modules/Workflow/WorkflowEngineRoutingTest.php`
Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add src/backend/tests/Feature/Modules/Workflow/WorkflowEngineRoutingTest.php src/backend/app/Modules/Workflow/Application/Services/WorkflowEngine.php src/backend/app/Modules/Workflow/Application/CommandHandlers/SubmitWorkflowRequestHandler.php src/backend/app/Modules/Workflow/Application/CommandHandlers/ApproveWorkflowStepHandler.php src/backend/app/Modules/Workflow/Infrastructure/Http/Requests/CreateWorkflowTemplateRequest.php src/backend/app/Modules/Workflow/Infrastructure/Persistence/Repositories/EloquentWorkflowTemplateRepository.php
git commit -m "feat: add workflow engine routing"
```

### Task 10: Register engine services and expose new template/request resource fields

**Files:**
- Create: `src/backend/app/Modules/Workflow/Infrastructure/Providers/WorkflowServiceProvider.php`
- Modify: `src/backend/app/Providers/AppServiceProvider.php`
- Modify: `src/backend/app/Modules/Workflow/Infrastructure/Http/Resources/WorkflowTemplateResource.php`
- Modify: `src/backend/app/Modules/Workflow/Infrastructure/Http/Resources/WorkflowRequestResource.php`

- [ ] **Step 1: Write failing feature assertion for new resource fields**

Add to `WorkflowEngineRoutingTest.php` this extra assertion after the `show` request:

```php
$show->assertJsonStructure([
    'data' => [
        'context',
        'resolved_approvers',
        'actions' => [
            '*' => ['resolved_approvers', 'delegation_map'],
        ],
    ],
]);
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker compose exec -T app php artisan test --compact src/backend/tests/Feature/Modules/Workflow/WorkflowEngineRoutingTest.php`
Expected: FAIL because resources omit fields

- [ ] **Step 3: Register services in provider**

```php
<?php

namespace App\Modules\Workflow\Infrastructure\Providers;

use App\Modules\Leave\Application\Workflow\LeaveRequestSubjectProvider;
use App\Modules\Workflow\Application\Resolvers\DepartmentHeadResolver;
use App\Modules\Workflow\Application\Resolvers\DirectManagerResolver;
use App\Modules\Workflow\Application\Resolvers\SpecificUserResolver;
use App\Modules\Workflow\Application\Services\ResolverRegistry;
use App\Modules\Workflow\Application\Services\SubjectDataProviderRegistry;
use Illuminate\Support\ServiceProvider;

final class WorkflowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ResolverRegistry::class, function () {
            $registry = new ResolverRegistry();
            $registry->register(new SpecificUserResolver());
            $registry->register(new DirectManagerResolver());
            $registry->register(new DepartmentHeadResolver());
            return $registry;
        });

        $this->app->singleton(SubjectDataProviderRegistry::class, function ($app) {
            $registry = new SubjectDataProviderRegistry();
            $registry->register($app->make(LeaveRequestSubjectProvider::class));
            return $registry;
        });
    }
}
```

Register this provider in `src/backend/app/Providers/AppServiceProvider.php` using the repo’s current bootstrapping pattern.

- [ ] **Step 4: Expose resource fields**

Add to `WorkflowRequestResource`:

```php
'context' => $r->context(),
'resolved_approvers' => $r->resolvedApprovers(),
```

Add to each action mapping:

```php
'resolved_approvers' => $a->resolvedApprovers(),
'delegation_map' => $a->delegationMap(),
```

Expose `resolver_type`, `resolver_config`, and `condition` in `WorkflowTemplateResource` step payload.

- [ ] **Step 5: Run test to verify it passes**

Run: `docker compose exec -T app php artisan test --compact src/backend/tests/Feature/Modules/Workflow/WorkflowEngineRoutingTest.php`
Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add src/backend/app/Modules/Workflow/Infrastructure/Providers/WorkflowServiceProvider.php src/backend/app/Providers/AppServiceProvider.php src/backend/app/Modules/Workflow/Infrastructure/Http/Resources/WorkflowTemplateResource.php src/backend/app/Modules/Workflow/Infrastructure/Http/Resources/WorkflowRequestResource.php src/backend/tests/Feature/Modules/Workflow/WorkflowEngineRoutingTest.php
git commit -m "feat: register workflow engine services"
```

### Task 11: Integrate Leave submit path with workflow template code

**Files:**
- Modify: `src/backend/app/Modules/Leave/Infrastructure/Http/Controllers/LeaveRequestController.php`
- Modify: `src/backend/app/Modules/Leave/Infrastructure/Http/Requests/SubmitLeaveRequest.php`
- Modify: `src/backend/app/Modules/Leave/Infrastructure/Http/Resources/LeaveRequestResource.php`
- Test: `src/backend/tests/Feature/Modules/Leave/LeaveWorkflowIntegrationTest.php`

- [ ] **Step 1: Write failing integration test**

```php
<?php

namespace Tests\Feature\Modules\Leave;

use App\Modules\Identity\Infrastructure\Persistence\Eloquent\UserModel;
use App\Modules\Leave\Infrastructure\Persistence\Eloquent\LeaveTypeModel;
use App\Modules\Workflow\Infrastructure\Persistence\Eloquent\WorkflowRequestModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LeaveWorkflowIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_submit_leave_creates_workflow_request_when_template_code_present(): void
    {
        $user = UserModel::factory()->create();
        Sanctum::actingAs($user);

        $leaveType = LeaveTypeModel::factory()->create([
            'workflow_template_code' => 'LEAVE-APPROVAL',
        ]);

        $response = $this->postJson('/api/v1/leave-requests', [
            'leave_type_id' => $leaveType->id,
            'employee_id' => '11111111-1111-1111-1111-111111111111',
            'start_at' => '2026-07-05',
            'end_at' => '2026-07-06',
            'duration_unit' => 'day',
            'duration_minutes' => 960,
            'reason' => 'Nghỉ phép',
        ]);

        $response->assertCreated();
        self::assertDatabaseCount('workflow_requests', 1);
        $request = WorkflowRequestModel::first();
        self::assertSame('leave_request', $request->subject_type);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker compose exec -T app php artisan test --compact src/backend/tests/Feature/Modules/Leave/LeaveWorkflowIntegrationTest.php`
Expected: FAIL because leave submit does not create workflow request

- [ ] **Step 3: Modify leave submit path minimally**

In `LeaveRequestController::store()` or the existing leave submit handler path, after creating the leave request:

```php
if ($leaveType->workflow_template_code) {
    $template = WorkflowTemplateModel::where('code', $leaveType->workflow_template_code)->first();
    if ($template) {
        $submitWorkflow->handle(new SubmitWorkflowRequestCommand(
            $template->id,
            'leave_request',
            $leaveRequest->id()->value(),
            (string) $request->user()->id,
        ));
    }
}
```

Add `workflow_template_code` to the leave type resource if the API already exposes leave type config.

- [ ] **Step 4: Run test to verify it passes**

Run: `docker compose exec -T app php artisan test --compact src/backend/tests/Feature/Modules/Leave/LeaveWorkflowIntegrationTest.php`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add src/backend/tests/Feature/Modules/Leave/LeaveWorkflowIntegrationTest.php src/backend/app/Modules/Leave/Infrastructure/Http/Controllers/LeaveRequestController.php src/backend/app/Modules/Leave/Infrastructure/Http/Requests/SubmitLeaveRequest.php src/backend/app/Modules/Leave/Infrastructure/Http/Resources/LeaveRequestResource.php
git commit -m "feat: start leave workflow on submit"
```

### Task 12: Integrate Leave approve/reject via workflow events

**Files:**
- Create: `src/backend/app/Modules/Leave/Application/Workflow/Listeners/SyncLeaveRequestOnWorkflowApproved.php`
- Create: `src/backend/app/Modules/Leave/Application/Workflow/Listeners/SyncLeaveRequestOnWorkflowRejected.php`
- Modify: `src/backend/app/Providers/AppServiceProvider.php`
- Test: `src/backend/tests/Feature/Modules/Leave/LeaveWorkflowApprovalSyncTest.php`

- [ ] **Step 1: Write failing approval sync test**

```php
<?php

namespace Tests\Feature\Modules\Leave;

use App\Modules\Identity\Infrastructure\Persistence\Eloquent\UserModel;
use App\Modules\Leave\Infrastructure\Persistence\Eloquent\LeaveRequestModel;
use App\Modules\Workflow\Domain\Events\WorkflowApproved;
use App\Modules\Workflow\Domain\Events\WorkflowRejected;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class LeaveWorkflowApprovalSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_workflow_approved_event_approves_leave_request(): void
    {
        $leave = LeaveRequestModel::factory()->create(['status' => 'pending']);
        Event::dispatch(new WorkflowApproved([
            'request_id' => 'workflow-1',
            'subject_type' => 'leave_request',
            'subject_id' => $leave->id,
            'actor_id' => 'approver-1',
        ]));

        self::assertDatabaseHas('leave_requests', ['id' => $leave->id, 'status' => 'approved']);
    }

    public function test_workflow_rejected_event_rejects_leave_request(): void
    {
        $leave = LeaveRequestModel::factory()->create(['status' => 'pending']);
        Event::dispatch(new WorkflowRejected([
            'request_id' => 'workflow-1',
            'subject_type' => 'leave_request',
            'subject_id' => $leave->id,
            'actor_id' => 'approver-1',
            'comment' => 'not allowed',
        ]));

        self::assertDatabaseHas('leave_requests', ['id' => $leave->id, 'status' => 'rejected']);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `docker compose exec -T app php artisan test --compact src/backend/tests/Feature/Modules/Leave/LeaveWorkflowApprovalSyncTest.php`
Expected: FAIL because listeners are missing

- [ ] **Step 3: Add listeners**

```php
<?php

namespace App\Modules\Leave\Application\Workflow\Listeners;

use App\Modules\Leave\Application\Commands\LeaveRequest\ApproveLeaveRequestCommand;
use App\Modules\Leave\Application\CommandHandlers\LeaveRequest\ApproveLeaveRequestHandler;
use App\Modules\Workflow\Domain\Events\WorkflowApproved;

final class SyncLeaveRequestOnWorkflowApproved
{
    public function __construct(private readonly ApproveLeaveRequestHandler $handler) {}

    public function handle(WorkflowApproved $event): void
    {
        if (($event->payload['subject_type'] ?? null) !== 'leave_request') {
            return;
        }

        $this->handler->handle(new ApproveLeaveRequestCommand(
            $event->payload['subject_id'],
            $event->payload['actor_id'] ?? 'system',
        ));
    }
}
```

```php
<?php

namespace App\Modules\Leave\Application\Workflow\Listeners;

use App\Modules\Leave\Application\Commands\LeaveRequest\RejectLeaveRequestCommand;
use App\Modules\Leave\Application\CommandHandlers\LeaveRequest\RejectLeaveRequestHandler;
use App\Modules\Workflow\Domain\Events\WorkflowRejected;

final class SyncLeaveRequestOnWorkflowRejected
{
    public function __construct(private readonly RejectLeaveRequestHandler $handler) {}

    public function handle(WorkflowRejected $event): void
    {
        if (($event->payload['subject_type'] ?? null) !== 'leave_request') {
            return;
        }

        $this->handler->handle(new RejectLeaveRequestCommand(
            $event->payload['subject_id'],
            $event->payload['actor_id'] ?? 'system',
            $event->payload['comment'] ?? 'Rejected by workflow',
        ));
    }
}
```

Register both listeners in `AppServiceProvider::boot()` using `Event::listen(...)`.

- [ ] **Step 4: Ensure workflow events include subject metadata**

Where `WorkflowApproved` and `WorkflowRejected` are created, include payload keys:

```php
[
    'request_id' => $this->id->value(),
    'subject_type' => $this->subjectType,
    'subject_id' => $this->subjectId,
    'actor_id' => $actorId,
    'comment' => $comment,
]
```

- [ ] **Step 5: Run tests to verify they pass**

Run: `docker compose exec -T app php artisan test --compact src/backend/tests/Feature/Modules/Leave/LeaveWorkflowApprovalSyncTest.php`
Expected: PASS

- [ ] **Step 6: Commit**

```bash
git add src/backend/tests/Feature/Modules/Leave/LeaveWorkflowApprovalSyncTest.php src/backend/app/Modules/Leave/Application/Workflow/Listeners/SyncLeaveRequestOnWorkflowApproved.php src/backend/app/Modules/Leave/Application/Workflow/Listeners/SyncLeaveRequestOnWorkflowRejected.php src/backend/app/Providers/AppServiceProvider.php src/backend/app/Modules/Workflow/Domain/Aggregates/WorkflowRequest/WorkflowRequest.php
git commit -m "feat: sync leave status from workflow events"
```

### Task 13: Verify end-to-end workflow + leave scenario and run full suite

**Files:**
- Modify if needed: files touched in Tasks 1–12
- Test: `src/backend/tests/Feature/Modules/Leave/LeaveWorkflowIntegrationTest.php`
- Test: `src/backend/tests/Feature/Modules/Workflow/WorkflowEngineRoutingTest.php`

- [ ] **Step 1: Add one end-to-end scenario test if still missing**

If not already covered, add this to `LeaveWorkflowIntegrationTest.php`:

```php
public function test_leave_workflow_approval_end_to_end(): void
{
    // submit leave request with workflow template code
    // create workflow request
    // approve workflow request through api
    // assert leave request becomes approved
    // assert balance deducted once
}
```

Use the existing leave fixtures/seed factories already present in the module; do not invent a second flow if one exists.

- [ ] **Step 2: Run targeted workflow and leave tests**

Run: `docker compose exec -T app php artisan test --compact src/backend/tests/Feature/Modules/Workflow src/backend/tests/Feature/Modules/Leave src/backend/tests/Unit/Modules/Workflow`
Expected: PASS

- [ ] **Step 3: Run full backend suite**

Run: `docker compose exec -T app php artisan test --compact`
Expected: PASS with full suite summary

- [ ] **Step 4: Commit final integration pass**

```bash
git add src/backend/tests/Feature/Modules/Workflow src/backend/tests/Feature/Modules/Leave src/backend/tests/Unit/Modules/Workflow src/backend/app/Modules/Workflow src/backend/app/Modules/Leave src/backend/app/Providers/AppServiceProvider.php src/backend/database/migrations
git commit -m "feat: deliver workflow engine b1"
```

## Spec Coverage Check

- Condition evaluator → Task 1
- Delegation resolver + aggregate → Task 2
- Resolver registry + specific resolver → Task 3
- Schema changes and leave type workflow code → Task 4
- Domain/repository metadata for context + approvers → Task 5
- Subject data provider + leave provider → Task 6
- Built-in resolvers (`direct_manager`, `department_head`, role variants) → Task 7
- Delegation API → Task 8
- WorkflowEngine routing and submit/approve wiring → Task 9
- Service registration + resource exposure → Task 10
- Leave submit integration → Task 11
- Leave workflow approval/reject listeners → Task 12
- End-to-end + full suite verification → Task 13

No uncovered spec items remain. `B1.5` items (parallel steps, SLA/escalation) are intentionally excluded.
