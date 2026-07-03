<?php
namespace App\Modules\Training\Domain\Aggregates\TrainingCourse;

class TrainingCourse {
    private array $recordedEvents = [];
    private function __construct(
        private readonly TrainingCourseId $id, private string $code, private string $name,
        private ?string $description, private ?string $category, private ?int $defaultDurationHours,
        private ?int $maxParticipants, private bool $active,
    ) {}
    public static function create(TrainingCourseId $id, string $code, string $name, ?string $description = null, ?string $category = null, ?int $defaultDurationHours = null, ?int $maxParticipants = null): self {
        return new self($id, $code, $name, $description, $category, $defaultDurationHours, $maxParticipants, true);
    }
    public static function reconstitute(TrainingCourseId $id, string $code, string $name, ?string $description, ?string $category, ?int $defaultDurationHours, ?int $maxParticipants, bool $active): self {
        return new self($id, $code, $name, $description, $category, $defaultDurationHours, $maxParticipants, $active);
    }
    public function update(string $code, string $name, ?string $description, ?string $category, ?int $defaultDurationHours, ?int $maxParticipants): void {
        $this->code = $code; $this->name = $name; $this->description = $description; $this->category = $category; $this->defaultDurationHours = $defaultDurationHours; $this->maxParticipants = $maxParticipants;
    }
    public function deactivate(): void { if (!$this->active) throw new \RuntimeException('Course already inactive'); $this->active = false; }
    public function getId(): TrainingCourseId { return $this->id; }
    public function getCode(): string { return $this->code; }
    public function getName(): string { return $this->name; }
    public function getDescription(): ?string { return $this->description; }
    public function getCategory(): ?string { return $this->category; }
    public function getDefaultDurationHours(): ?int { return $this->defaultDurationHours; }
    public function getMaxParticipants(): ?int { return $this->maxParticipants; }
    public function isActive(): bool { return $this->active; }
    public function popRecordedEvents(): array { $e=$this->recordedEvents; $this->recordedEvents=[]; return $e; }
}
