<?php

namespace App\Modules\Performance\Domain\Aggregates\PerformanceCycle;

use App\Modules\Performance\Domain\Events\CycleActivated;
use App\Modules\Performance\Domain\Events\CycleCompleted;
use App\Modules\Performance\Domain\Events\CycleCreated;
use App\Modules\Performance\Domain\Exceptions\InvalidStatusTransitionException;
use App\Modules\Performance\Domain\ValueObjects\CycleStatus;

class PerformanceCycle
{
    private array $recordedEvents = [];

    private function __construct(
        private readonly PerformanceCycleId $id,
        private string $code,
        private string $name,
        private ?string $description,
        private \DateTimeImmutable $startDate,
        private \DateTimeImmutable $endDate,
        private CycleStatus $status,
        private array $scoringRules,
        private ?string $workflowRequestId,
    ) {}

    public static function create(PerformanceCycleId $id, string $code, string $name, ?string $description, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate, array $scoringRules): self
    {
        if ($startDate >= $endDate) { throw new \InvalidArgumentException('Start date must be before end date'); }
        $c = new self($id, $code, $name, $description, $startDate, $endDate, CycleStatus::Draft, $scoringRules, null);
        $c->recordedEvents[] = new CycleCreated($c->id->value);
        return $c;
    }

    public static function reconstitute(PerformanceCycleId $id, string $code, string $name, ?string $description, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate, CycleStatus $status, array $scoringRules, ?string $workflowRequestId): self
    {
        return new self($id, $code, $name, $description, $startDate, $endDate, $status, $scoringRules, $workflowRequestId);
    }

    public function activate(): void
    {
        if (!$this->status->canTransitionTo(CycleStatus::Active)) {
            throw new InvalidStatusTransitionException($this->status->value, CycleStatus::Active->value);
        }
        $this->status = CycleStatus::Active;
        $this->recordedEvents[] = new CycleActivated($this->id->value);
    }

    public function complete(): void
    {
        if (!$this->status->canTransitionTo(CycleStatus::Completed)) {
            throw new InvalidStatusTransitionException($this->status->value, CycleStatus::Completed->value);
        }
        $this->status = CycleStatus::Completed;
        $this->recordedEvents[] = new CycleCompleted($this->id->value);
    }

    public function cancel(): void
    {
        if (!$this->status->canTransitionTo(CycleStatus::Cancelled)) {
            throw new InvalidStatusTransitionException($this->status->value, CycleStatus::Cancelled->value);
        }
        $this->status = CycleStatus::Cancelled;
    }

    public function popRecordedEvents(): array { $e=$this->recordedEvents; $this->recordedEvents=[]; return $e; }
    public function getId(): PerformanceCycleId { return $this->id; }
    public function getCode(): string { return $this->code; }
    public function getName(): string { return $this->name; }
    public function getDescription(): ?string { return $this->description; }
    public function getStartDate(): \DateTimeImmutable { return $this->startDate; }
    public function getEndDate(): \DateTimeImmutable { return $this->endDate; }
    public function getStatus(): CycleStatus { return $this->status; }
    public function getScoringRules(): array { return $this->scoringRules; }
    public function getWorkflowRequestId(): ?string { return $this->workflowRequestId; }
    public function setWorkflowRequestId(string $id): void { $this->workflowRequestId = $id; }
}
