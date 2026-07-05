<?php

namespace App\Modules\Offboarding\Domain\Repositories;

use App\Modules\Offboarding\Domain\Aggregates\OffboardingTask\OffboardingTask;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingTask\OffboardingTaskId;

interface OffboardingTaskRepositoryInterface
{
    public function findById(OffboardingTaskId $id): ?OffboardingTask;

    /** @return OffboardingTask[] */
    public function findByPlanId(string $planId): array;

    /** @return OffboardingTask[] */
    public function findByOwner(string $ownerType, string $ownerId): array;

    public function findByApprovalWorkflowRequestId(string $requestId): ?OffboardingTask;

    public function save(OffboardingTask $task): void;

    public function delete(OffboardingTaskId $id): void;
}
