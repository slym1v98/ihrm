<?php
namespace App\Modules\Training\Domain\Aggregates\TrainingSession;

use App\Modules\Training\Domain\Events\SessionScheduled;
use App\Modules\Training\Domain\Exceptions\SessionFullException;
use App\Modules\Training\Domain\Exceptions\InvalidEnrollmentStatusException;
use App\Modules\Training\Domain\ValueObjects\SessionStatus;

class TrainingSession {
    private array $recordedEvents = [];
    private function __construct(
        private readonly TrainingSessionId $id, private readonly string $courseId,
        private string $code, private string $name, private \DateTimeImmutable $startDate,
        private \DateTimeImmutable $endDate, private ?string $location, private ?string $instructor,
        private ?int $maxParticipants, private SessionStatus $status,
    ) {}
    public static function schedule(TrainingSessionId $id, string $courseId, string $code, string $name, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate, ?string $location = null, ?string $instructor = null, ?int $maxParticipants = null): self {
        if ($startDate >= $endDate) throw new \InvalidArgumentException('Start must be before end');
        $s = new self($id, $courseId, $code, $name, $startDate, $endDate, $location, $instructor, $maxParticipants, SessionStatus::Scheduled);
        $s->recordedEvents[] = new SessionScheduled($id->value);
        return $s;
    }
    public static function reconstitute(TrainingSessionId $id, string $courseId, string $code, string $name, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate, ?string $location, ?string $instructor, ?int $maxParticipants, SessionStatus $status): self {
        return new self($id, $courseId, $code, $name, $startDate, $endDate, $location, $instructor, $maxParticipants, $status);
    }
    public function assertCanEnroll(int $currentCount): void {
        if ($this->maxParticipants !== null && $currentCount >= $this->maxParticipants) throw new SessionFullException();
    }
    public function start(): void { if (!$this->status->canTransitionTo(SessionStatus::Active)) throw new InvalidEnrollmentStatusException($this->status->value, SessionStatus::Active->value); $this->status = SessionStatus::Active; }
    public function complete(): void { if (!$this->status->canTransitionTo(SessionStatus::Completed)) throw new InvalidEnrollmentStatusException($this->status->value, SessionStatus::Completed->value); $this->status = SessionStatus::Completed; }
    public function cancel(): void { if (!$this->status->canTransitionTo(SessionStatus::Cancelled)) throw new InvalidEnrollmentStatusException($this->status->value, SessionStatus::Cancelled->value); $this->status = SessionStatus::Cancelled; }
    public function update(string $code, string $name, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate, ?string $location, ?string $instructor, ?int $maxParticipants): void {
        if ($startDate >= $endDate) throw new \InvalidArgumentException('Start must be before end');
        $this->code = $code; $this->name = $name; $this->startDate = $startDate; $this->endDate = $endDate; $this->location = $location; $this->instructor = $instructor; $this->maxParticipants = $maxParticipants;
    }
    public function getId(): TrainingSessionId { return $this->id; }
    public function getCourseId(): string { return $this->courseId; }
    public function getCode(): string { return $this->code; }
    public function getName(): string { return $this->name; }
    public function getStartDate(): \DateTimeImmutable { return $this->startDate; }
    public function getEndDate(): \DateTimeImmutable { return $this->endDate; }
    public function getLocation(): ?string { return $this->location; }
    public function getInstructor(): ?string { return $this->instructor; }
    public function getMaxParticipants(): ?int { return $this->maxParticipants; }
    public function getStatus(): SessionStatus { return $this->status; }
    public function popRecordedEvents(): array { $e=$this->recordedEvents; $this->recordedEvents=[]; return $e; }
}
