<?php
namespace App\Modules\Training\Domain\Aggregates\TrainingResult;

use App\Modules\Training\Domain\Events\ResultRecorded;
use App\Modules\Training\Domain\Events\TrainingCompleted;

class TrainingResult {
    private array $recordedEvents = [];
    private function __construct(
        private readonly TrainingResultId $id, private readonly string $enrollmentId,
        private ?float $score, private ?bool $passed, private ?string $certificateCode,
        private ?\DateTimeImmutable $issuedAt, private ?string $notes,
    ) {}
    public static function record(TrainingResultId $id, string $enrollmentId, ?float $score = null, ?bool $passed = null, ?string $certificateCode = null, ?string $notes = null): self {
        $r = new self($id, $enrollmentId, $score, $passed, $certificateCode, new \DateTimeImmutable(), $notes);
        $r->recordedEvents[] = new ResultRecorded($id->value);
        $r->recordedEvents[] = new TrainingCompleted($enrollmentId);
        return $r;
    }
    public static function reconstitute(TrainingResultId $id, string $enrollmentId, ?float $score, ?bool $passed, ?string $certificateCode, ?\DateTimeImmutable $issuedAt, ?string $notes): self {
        return new self($id, $enrollmentId, $score, $passed, $certificateCode, $issuedAt, $notes);
    }
    public function getId(): TrainingResultId { return $this->id; }
    public function getEnrollmentId(): string { return $this->enrollmentId; }
    public function getScore(): ?float { return $this->score; }
    public function getPassed(): ?bool { return $this->passed; }
    public function getCertificateCode(): ?string { return $this->certificateCode; }
    public function getIssuedAt(): ?\DateTimeImmutable { return $this->issuedAt; }
    public function getNotes(): ?string { return $this->notes; }
    public function popRecordedEvents(): array { $e=$this->recordedEvents; $this->recordedEvents=[]; return $e; }
}
