<?php

namespace App\Modules\Performance\Domain\Aggregates\PerformanceReview;

use App\Modules\Performance\Domain\Events\HrReviewSubmitted;
use App\Modules\Performance\Domain\Events\ManagerReviewSubmitted;
use App\Modules\Performance\Domain\Events\ReviewCreated;
use App\Modules\Performance\Domain\Events\ReviewFinalized;
use App\Modules\Performance\Domain\Events\SelfAssessmentSubmitted;
use App\Modules\Performance\Domain\Exceptions\InvalidStatusTransitionException;
use App\Modules\Performance\Domain\ValueObjects\ReviewStatus;

class PerformanceReview
{
    private array $recordedEvents = [];

    private function __construct(
        private readonly PerformanceReviewId $id,
        private readonly string $cycleId,
        private readonly string $employeeId,
        private ?array $selfAssessment,
        private ?array $managerAssessment,
        private ?array $hrAssessment,
        private ?float $finalScore,
        private ReviewStatus $status,
        private ?\DateTimeImmutable $finalizedAt,
    ) {}

    public static function create(PerformanceReviewId $id, string $cycleId, string $employeeId): self
    {
        $r = new self($id, $cycleId, $employeeId, null, null, null, null, ReviewStatus::PendingSelf, null);
        $r->recordedEvents[] = new ReviewCreated($id->value, $employeeId);

        return $r;
    }

    public static function reconstitute(PerformanceReviewId $id, string $cycleId, string $employeeId, ?array $self, ?array $manager, ?array $hr, ?float $finalScore, ReviewStatus $status, ?\DateTimeImmutable $finalizedAt): self
    {
        return new self($id, $cycleId, $employeeId, $self, $manager, $hr, $finalScore, $status, $finalizedAt);
    }

    public function submitSelf(array $assessment): void
    {
        if (! $this->status->canTransitionTo(ReviewStatus::SelfCompleted)) {
            throw new InvalidStatusTransitionException($this->status->value, ReviewStatus::SelfCompleted->value);
        }
        $this->selfAssessment = $assessment;
        $this->status = ReviewStatus::SelfCompleted;
        $this->recordedEvents[] = new SelfAssessmentSubmitted($this->id->value, $this->employeeId);
    }

    public function submitManager(array $assessment): void
    {
        if (! $this->status->canTransitionTo(ReviewStatus::ManagerCompleted)) {
            throw new InvalidStatusTransitionException($this->status->value, ReviewStatus::ManagerCompleted->value);
        }
        $this->managerAssessment = $assessment;
        $this->status = ReviewStatus::ManagerCompleted;
        $this->recordedEvents[] = new ManagerReviewSubmitted($this->id->value, $this->employeeId);
    }

    public function submitHr(array $assessment): void
    {
        if (! $this->status->canTransitionTo(ReviewStatus::HrCompleted)) {
            throw new InvalidStatusTransitionException($this->status->value, ReviewStatus::HrCompleted->value);
        }
        $this->hrAssessment = $assessment;
        $this->status = ReviewStatus::HrCompleted;
        $this->recordedEvents[] = new HrReviewSubmitted($this->id->value, $this->employeeId);
    }

    public function finalize(?float $finalScore = null): void
    {
        if (! $this->status->canTransitionTo(ReviewStatus::Finalized)) {
            throw new InvalidStatusTransitionException($this->status->value, ReviewStatus::Finalized->value);
        }
        $this->status = ReviewStatus::Finalized;
        $this->finalizedAt = new \DateTimeImmutable;
        $this->finalScore = $finalScore;
        $this->recordedEvents[] = new ReviewFinalized($this->id->value, $this->employeeId);
    }

    public function popRecordedEvents(): array
    {
        $e = $this->recordedEvents;
        $this->recordedEvents = [];

        return $e;
    }

    public function getId(): PerformanceReviewId
    {
        return $this->id;
    }

    public function getCycleId(): string
    {
        return $this->cycleId;
    }

    public function getEmployeeId(): string
    {
        return $this->employeeId;
    }

    public function getSelfAssessment(): ?array
    {
        return $this->selfAssessment;
    }

    public function getManagerAssessment(): ?array
    {
        return $this->managerAssessment;
    }

    public function getHrAssessment(): ?array
    {
        return $this->hrAssessment;
    }

    public function getFinalScore(): ?float
    {
        return $this->finalScore;
    }

    public function getStatus(): ReviewStatus
    {
        return $this->status;
    }

    public function getFinalizedAt(): ?\DateTimeImmutable
    {
        return $this->finalizedAt;
    }
}
