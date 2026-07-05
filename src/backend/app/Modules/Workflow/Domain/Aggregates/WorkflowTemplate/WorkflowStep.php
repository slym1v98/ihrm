<?php

namespace App\Modules\Workflow\Domain\Aggregates\WorkflowTemplate;

use App\Modules\Workflow\Domain\ValueObjects\AssigneeType;

class WorkflowStep
{
    public function __construct(
        private WorkflowStepId $id,
        private int $stepOrder,
        private string $name,
        private AssigneeType $assigneeType,
        private ?string $assigneeId = null,
        private ?array $condition = null,
        private ?string $resolverType = null,
        private ?array $resolverConfig = null,
        private string $executionType = 'sequential',
        private ?float $escalationSlaHours = null,
        private ?string $escalationTargetType = null,
        private ?array $escalationTargetConfig = null,
        private ?array $formSchema = null,
    ) {}

    public function id(): WorkflowStepId
    {
        return $this->id;
    }

    public function stepOrder(): int
    {
        return $this->stepOrder;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function assigneeType(): AssigneeType
    {
        return $this->assigneeType;
    }

    public function assigneeId(): ?string
    {
        return $this->assigneeId;
    }

    public function condition(): ?array
    {
        return $this->condition;
    }

    public function resolverType(): ?string
    {
        return $this->resolverType;
    }

    public function resolverConfig(): ?array
    {
        return $this->resolverConfig;
    }

    public function executionType(): string
    {
        return $this->executionType;
    }

    public function escalationSlaHours(): ?float
    {
        return $this->escalationSlaHours;
    }

    public function escalationTargetType(): ?string
    {
        return $this->escalationTargetType;
    }

    public function escalationTargetConfig(): ?array
    {
        return $this->escalationTargetConfig;
    }

    public function formSchema(): ?array
    {
        return $this->formSchema;
    }
}
