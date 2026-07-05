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
