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
