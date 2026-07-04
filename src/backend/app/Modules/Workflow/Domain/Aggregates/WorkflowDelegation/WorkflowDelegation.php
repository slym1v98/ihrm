<?php
namespace App\Modules\Workflow\Domain\Aggregates\WorkflowDelegation;
use Carbon\CarbonImmutable;
class WorkflowDelegation
{
    public function __construct(
        private WorkflowDelegationId $id,
        private string $delegatorId,
        private string $delegateId,
        private ?string $roleType,
        private CarbonImmutable $startAt,
        private CarbonImmutable $endAt,
        private bool $active,
        private ?string $createdBy = null,
    ) {}
    public function id(): WorkflowDelegationId { return $this->id; }
    public function delegatorId(): string { return $this->delegatorId; }
    public function delegateId(): string { return $this->delegateId; }
    public function roleType(): ?string { return $this->roleType; }
    public function startAt(): CarbonImmutable { return $this->startAt; }
    public function endAt(): CarbonImmutable { return $this->endAt; }
    public function active(): bool { return $this->active; }
    public function createdBy(): ?string { return $this->createdBy; }
    public function revoke(): void { $this->active = false; }
    public function isEffectiveAt(CarbonImmutable $at): bool { return $this->active && $at >= $this->startAt && $at <= $this->endAt; }
}
