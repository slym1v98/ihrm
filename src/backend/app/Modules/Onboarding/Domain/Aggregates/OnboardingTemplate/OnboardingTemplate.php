<?php

namespace App\Modules\Onboarding\Domain\Aggregates\OnboardingTemplate;

use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlan;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingPlan\OnboardingPlanId;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTask;
use App\Modules\Onboarding\Domain\Aggregates\OnboardingTask\OnboardingTaskId;
use App\Modules\Onboarding\Domain\ValueObjects\OwnerType;
use App\Modules\Onboarding\Domain\ValueObjects\TaskType;
use App\Modules\Onboarding\Domain\ValueObjects\TemplateRules;

class OnboardingTemplate
{
    private function __construct(
        private readonly OnboardingTemplateId $id,
        private string $code,
        private string $name,
        private TemplateRules $rules,
        private bool $active,
    ) {}

    public static function create(
        OnboardingTemplateId $id,
        string $code,
        string $name,
        TemplateRules $rules,
    ): self {
        return new self($id, $code, $name, $rules, true);
    }

    public static function reconstitute(
        OnboardingTemplateId $id,
        string $code,
        string $name,
        TemplateRules $rules,
        bool $active,
    ): self {
        return new self($id, $code, $name, $rules, $active);
    }

    public function update(string $code, string $name, TemplateRules $rules): void
    {
        $this->code = $code;
        $this->name = $name;
        $this->rules = $rules;
    }

    public function disable(): void
    {
        $this->active = false;
    }

    public function addTemplateTask(string $title, ?string $description, string $ownerType, string $ownerId, ?int $dueDays, bool $requiresApproval, bool $isPreStart, int $sortOrder): void
    {
        $this->rules->addTask($title, $description, $ownerType, $ownerId, $dueDays, $requiresApproval, $isPreStart, $sortOrder);
    }

    public function removeTemplateTask(int $sortOrder): void
    {
        $this->rules->removeTask($sortOrder);
    }

    public function matches(?string $departmentId, ?string $positionId, ?string $locationId, ?string $employmentType): bool
    {
        return $this->active && $this->rules->matches($departmentId, $positionId, $locationId, $employmentType);
    }

    public function generatePlan(
        OnboardingPlanId $planId,
        string $employeeId,
        ?string $candidateId,
        \DateTimeImmutable $startDate,
    ): OnboardingPlan {
        $plan = OnboardingPlan::create(
            $planId,
            $employeeId,
            $candidateId,
            $this->id->value,
            $startDate,
        );

        foreach ($this->rules->getTasks() as $taskDef) {
            $taskId = OnboardingTaskId::generate();
            $dueDate = $taskDef['due_days'] !== null
                ? $startDate->modify(($taskDef['due_days'] >= 0 ? '+' : '').$taskDef['due_days'].' days')
                : null;

            $task = OnboardingTask::create(
                $taskId,
                $planId->value,
                TaskType::SystemDefined,
                OwnerType::from($taskDef['owner_type']),
                $taskDef['owner_id'],
                $taskDef['title'],
                $taskDef['description'] ?? null,
                $dueDate,
                $taskDef['requires_approval'] ?? false,
                $taskDef['is_pre_start'] ?? false,
                $taskDef['sort_order'] ?? 0,
            );

            $plan->addGeneratedTask($task);
        }

        return $plan;
    }

    public function getId(): OnboardingTemplateId
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRules(): TemplateRules
    {
        return $this->rules;
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}
