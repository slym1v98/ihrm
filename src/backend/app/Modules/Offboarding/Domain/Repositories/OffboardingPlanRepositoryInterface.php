<?php

namespace App\Modules\Offboarding\Domain\Repositories;

use App\Modules\Offboarding\Domain\Aggregates\OffboardingPlan\OffboardingPlan;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingPlan\OffboardingPlanId;

interface OffboardingPlanRepositoryInterface
{
    public function findById(OffboardingPlanId $id): ?OffboardingPlan;
    /** @return OffboardingPlan[] */
    public function findByEmployeeId(string $employeeId): array;
    public function findByWorkflowRequestId(string $workflowRequestId): ?OffboardingPlan;
    /** @return OffboardingPlan[] */
    public function all(): array;
    public function save(OffboardingPlan $plan): void;
    public function delete(OffboardingPlanId $id): void;
}
