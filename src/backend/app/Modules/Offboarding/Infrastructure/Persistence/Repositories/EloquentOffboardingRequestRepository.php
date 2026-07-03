<?php

namespace App\Modules\Offboarding\Infrastructure\Persistence\Repositories;

use App\Modules\Offboarding\Domain\Aggregates\OffboardingRequest\OffboardingRequest;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingRequest\OffboardingRequestId;
use App\Modules\Offboarding\Domain\Repositories\OffboardingRequestRepositoryInterface;
use App\Modules\Offboarding\Domain\ValueObjects\OffboardingRequestStatus;
use App\Modules\Offboarding\Domain\ValueObjects\OffboardingRequestType;
use App\Modules\Offboarding\Infrastructure\Persistence\Eloquent\OffboardingRequestModel;

class EloquentOffboardingRequestRepository implements OffboardingRequestRepositoryInterface
{
    public function findById(OffboardingRequestId $id): ?OffboardingRequest
    {
        $m = OffboardingRequestModel::find($id->value);
        return $m ? $this->toDomain($m) : null;
    }

    public function findByEmployeeId(string $employeeId): array
    {
        return OffboardingRequestModel::where('employee_id', $employeeId)
            ->get()->map(fn($m) => $this->toDomain($m))->toArray();
    }

    public function findByWorkflowRequestId(string $workflowRequestId): ?OffboardingRequest
    {
        $m = OffboardingRequestModel::where('workflow_request_id', $workflowRequestId)->first();
        return $m ? $this->toDomain($m) : null;
    }

    public function all(): array
    {
        return OffboardingRequestModel::all()->map(fn($m) => $this->toDomain($m))->toArray();
    }

    public function save(OffboardingRequest $request): void
    {
        OffboardingRequestModel::updateOrCreate(
            ['id' => $request->getId()->value],
            [
                'employee_id' => $request->getEmployeeId(),
                'type' => $request->getType()->value,
                'reason' => $request->getReason(),
                'requested_last_working_date' => $request->getRequestedLastWorkingDate()->format('Y-m-d'),
                'approved_last_working_date' => $request->getApprovedLastWorkingDate()?->format('Y-m-d'),
                'status' => $request->getStatus()->value,
                'workflow_request_id' => $request->getWorkflowRequestId(),
            ]
        );
    }

    private function toDomain(OffboardingRequestModel $m): OffboardingRequest
    {
        return OffboardingRequest::reconstitute(
            OffboardingRequestId::fromString($m->id), $m->employee_id,
            OffboardingRequestType::from($m->type), $m->reason,
            new \DateTimeImmutable($m->requested_last_working_date),
            $m->approved_last_working_date ? new \DateTimeImmutable($m->approved_last_working_date) : null,
            OffboardingRequestStatus::from($m->status), $m->workflow_request_id,
        );
    }
}
