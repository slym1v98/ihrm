<?php

namespace App\Modules\Onboarding\Domain\ValueObjects;

class TemplateRules
{
    /** @var string[] */
    private array $departments;

    /** @var string[] */
    private array $positions;

    /** @var string[] */
    private array $locations;

    /** @var string[] */
    private array $employmentTypes;

    /** @var array */
    private array $tasks;

    public function __construct(
        array $departments = [],
        array $positions = [],
        array $locations = [],
        array $employmentTypes = [],
        array $tasks = []
    ) {
        $this->departments = $departments;
        $this->positions = $positions;
        $this->locations = $locations;
        $this->employmentTypes = $employmentTypes;
        $this->tasks = $tasks;
    }

    public function matches(?string $departmentId, ?string $positionId, ?string $locationId, ?string $employmentType): bool
    {
        return (empty($this->departments) || ($departmentId && in_array($departmentId, $this->departments, true)))
            && (empty($this->positions) || ($positionId && in_array($positionId, $this->positions, true)))
            && (empty($this->locations) || ($locationId && in_array($locationId, $this->locations, true)))
            && (empty($this->employmentTypes) || ($employmentType && in_array($employmentType, $this->employmentTypes, true)));
    }

    public function getTasks(): array
    {
        return $this->tasks;
    }

    public function addTask(string $title, ?string $description, string $ownerType, string $ownerId, ?int $dueDays, bool $requiresApproval, bool $isPreStart, int $sortOrder): void
    {
        $this->tasks[] = [
            'title' => $title,
            'description' => $description,
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'due_days' => $dueDays,
            'requires_approval' => $requiresApproval,
            'is_pre_start' => $isPreStart,
            'sort_order' => $sortOrder,
        ];
    }

    public function removeTask(int $sortOrder): void
    {
        $this->tasks = array_values(
            array_filter($this->tasks, fn($t) => ($t['sort_order'] ?? 0) !== $sortOrder)
        );
    }

    public function toArray(): array
    {
        return [
            'departments' => $this->departments,
            'positions' => $this->positions,
            'locations' => $this->locations,
            'employment_types' => $this->employmentTypes,
            'tasks' => $this->tasks,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['departments'] ?? [],
            $data['positions'] ?? [],
            $data['locations'] ?? [],
            $data['employment_types'] ?? [],
            $data['tasks'] ?? [],
        );
    }
}
