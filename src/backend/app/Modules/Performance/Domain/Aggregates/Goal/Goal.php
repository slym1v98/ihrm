<?php

namespace App\Modules\Performance\Domain\Aggregates\Goal;

use App\Modules\Performance\Domain\Events\GoalCompleted;
use App\Modules\Performance\Domain\Events\GoalCreated;
use App\Modules\Performance\Domain\Exceptions\InvalidStatusTransitionException;
use App\Modules\Performance\Domain\ValueObjects\GoalStatus;

class Goal
{
    private array $recordedEvents = [];

    private function __construct(
        private readonly GoalId $id,
        private readonly string $cycleId,
        private readonly ?string $employeeId,
        private string $title,
        private ?string $description,
        private float $weight,
        private ?string $targetValue,
        private ?string $actualValue,
        private GoalStatus $status,
        private int $sortOrder,
    ) {}

    public static function create(GoalId $id, string $cycleId, ?string $employeeId, string $title, ?string $description, float $weight, ?string $targetValue, int $sortOrder = 0): self
    {
        $g = new self($id, $cycleId, $employeeId, $title, $description, $weight, $targetValue, null, GoalStatus::Active, $sortOrder);
        $g->recordedEvents[] = new GoalCreated($id->value);
        return $g;
    }

    public static function reconstitute(GoalId $id, string $cycleId, ?string $employeeId, string $title, ?string $description, float $weight, ?string $targetValue, ?string $actualValue, GoalStatus $status, int $sortOrder): self
    {
        return new self($id, $cycleId, $employeeId, $title, $description, $weight, $targetValue, $actualValue, $status, $sortOrder);
    }

    public function update(string $title, ?string $description, float $weight, ?string $targetValue): void
    {
        if ($this->status !== GoalStatus::Active) {
            throw new \RuntimeException('Cannot update non-active goal');
        }
        $this->title = $title;
        $this->description = $description;
        $this->weight = $weight;
        $this->targetValue = $targetValue;
    }

    public function complete(?string $actualValue = null): void
    {
        if (!$this->status->canTransitionTo(GoalStatus::Completed)) {
            throw new InvalidStatusTransitionException($this->status->value, GoalStatus::Completed->value);
        }
        $this->actualValue = $actualValue;
        $this->status = GoalStatus::Completed;
        $this->recordedEvents[] = new GoalCompleted($this->id->value);
    }

    public function archive(): void
    {
        if (!$this->status->canTransitionTo(GoalStatus::Archived)) {
            throw new InvalidStatusTransitionException($this->status->value, GoalStatus::Archived->value);
        }
        $this->status = GoalStatus::Archived;
    }

    public function popRecordedEvents(): array { $e=$this->recordedEvents; $this->recordedEvents=[]; return $e; }
    public function getId(): GoalId { return $this->id; }
    public function getCycleId(): string { return $this->cycleId; }
    public function getEmployeeId(): ?string { return $this->employeeId; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function getWeight(): float { return $this->weight; }
    public function getTargetValue(): ?string { return $this->targetValue; }
    public function getActualValue(): ?string { return $this->actualValue; }
    public function getStatus(): GoalStatus { return $this->status; }
    public function getSortOrder(): int { return $this->sortOrder; }
}
