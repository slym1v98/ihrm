<?php

namespace App\Modules\Workflow\Domain\Repositories;

use App\Modules\Workflow\Domain\Aggregates\WorkflowDelegation\WorkflowDelegation;
use App\Modules\Workflow\Domain\Aggregates\WorkflowDelegation\WorkflowDelegationId;
use Carbon\CarbonImmutable;

interface WorkflowDelegationRepositoryInterface
{
    public function findById(WorkflowDelegationId $id): ?WorkflowDelegation;

    /** @return WorkflowDelegation[] */
    public function findActiveForDelegator(string $delegatorId, CarbonImmutable $at, ?string $roleType = null): array;

    public function hasOverlap(string $delegatorId, CarbonImmutable $startAt, CarbonImmutable $endAt, ?string $roleType = null, ?string $ignoreId = null): bool;

    public function save(WorkflowDelegation $delegation): void;
}
