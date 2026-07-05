<?php

namespace App\Modules\Recruitment\Domain\Aggregates\RecruitmentRequisition;

use App\Modules\Recruitment\Domain\ValueObjects\RequisitionStatus;
use Carbon\CarbonImmutable;

class RecruitmentRequisition
{
    private function __construct(private readonly RecruitmentRequisitionId $id, private string $departmentId, private string $position, private int $headcount, private string $reason, private RequisitionStatus $status, private ?string $workflowRequestId, private ?CarbonImmutable $openedAt, private ?CarbonImmutable $closedAt, private string $createdBy) {}

    public static function create(RecruitmentRequisitionId $id, string $departmentId, string $position, int $headcount, string $reason, string $createdBy): self
    {
        return new self($id, $departmentId, $position, $headcount, $reason, RequisitionStatus::Draft, null, null, null, $createdBy);
    }

    public static function reconstitute(RecruitmentRequisitionId $id, string $departmentId, string $position, int $headcount, string $reason, RequisitionStatus $status, ?string $workflowRequestId, ?CarbonImmutable $openedAt, ?CarbonImmutable $closedAt, string $createdBy): self
    {
        return new self($id, $departmentId, $position, $headcount, $reason, $status, $workflowRequestId, $openedAt, $closedAt, $createdBy);
    }

    public function submit(string $workflowRequestId): void
    {
        if (! $this->status->canTransitionTo(RequisitionStatus::PendingApproval)) {
            throw new \InvalidArgumentException('Invalid requisition transition');
        } $this->status = RequisitionStatus::PendingApproval;
        $this->workflowRequestId = $workflowRequestId;
    }

    public function approve(CarbonImmutable $at): void
    {
        if (! $this->status->canTransitionTo(RequisitionStatus::Open)) {
            throw new \InvalidArgumentException('Invalid requisition transition');
        } $this->status = RequisitionStatus::Open;
        $this->openedAt = $at;
    }

    public function getId(): RecruitmentRequisitionId
    {
        return $this->id;
    }

    public function getDepartmentId(): string
    {
        return $this->departmentId;
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function getHeadcount(): int
    {
        return $this->headcount;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getStatus(): RequisitionStatus
    {
        return $this->status;
    }

    public function getWorkflowRequestId(): ?string
    {
        return $this->workflowRequestId;
    }

    public function getOpenedAt(): ?CarbonImmutable
    {
        return $this->openedAt;
    }

    public function getClosedAt(): ?CarbonImmutable
    {
        return $this->closedAt;
    }

    public function getCreatedBy(): string
    {
        return $this->createdBy;
    }
}
