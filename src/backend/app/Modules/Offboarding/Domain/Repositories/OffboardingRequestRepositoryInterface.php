<?php

namespace App\Modules\Offboarding\Domain\Repositories;

use App\Modules\Offboarding\Domain\Aggregates\OffboardingRequest\OffboardingRequest;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingRequest\OffboardingRequestId;

interface OffboardingRequestRepositoryInterface
{
    public function findById(OffboardingRequestId $id): ?OffboardingRequest;

    public function findByEmployeeId(string $employeeId): array;

    public function findByWorkflowRequestId(string $workflowRequestId): ?OffboardingRequest;

    public function all(): array;

    public function save(OffboardingRequest $request): void;
}
