<?php
namespace App\Modules\Recruitment\Domain\Aggregates\Interview;
use App\Modules\Recruitment\Domain\ValueObjects\InterviewStatus;
use Carbon\CarbonImmutable;
class Interview {
    private function __construct(private readonly InterviewId $id, private string $candidateId, private string $requisitionId, private array $interviewers, private CarbonImmutable $scheduledAt, private InterviewStatus $status, private array $scorecards, private ?string $notes) {}
    public static function schedule(InterviewId $id, string $candidateId, string $requisitionId, array $interviewers, CarbonImmutable $scheduledAt, ?string $notes = null): self { return new self($id,$candidateId,$requisitionId,$interviewers,$scheduledAt,InterviewStatus::Scheduled,[],$notes); }
    
    public static function reconstitute(InterviewId $id, string $candidateId, string $requisitionId, array $interviewers, \Carbon\CarbonImmutable $scheduledAt, InterviewStatus $status, array $scorecards, ?string $notes): self { return new self($id,$candidateId,$requisitionId,$interviewers,$scheduledAt,$status,$scorecards,$notes); }
    public function submitScorecard(string $interviewerId, int $score, string $comment, CarbonImmutable $at): void { foreach($this->scorecards as $sc){ if(($sc['interviewer_id'] ?? null)===$interviewerId) throw new \InvalidArgumentException('Scorecard already submitted'); } if($this->status===InterviewStatus::Cancelled) throw new \InvalidArgumentException('Cannot score cancelled interview'); $this->scorecards[]=['interviewer_id'=>$interviewerId,'score'=>$score,'comment'=>$comment,'submitted_at'=>$at->toDateTimeString()]; $this->status=InterviewStatus::Completed; }
    public function getId(): InterviewId { return $this->id; } public function getCandidateId(): string { return $this->candidateId; } public function getRequisitionId(): string { return $this->requisitionId; } public function getInterviewers(): array { return $this->interviewers; } public function getScheduledAt(): CarbonImmutable { return $this->scheduledAt; } public function getStatus(): InterviewStatus { return $this->status; } public function getScorecards(): array { return $this->scorecards; } public function getNotes(): ?string { return $this->notes; }
}
