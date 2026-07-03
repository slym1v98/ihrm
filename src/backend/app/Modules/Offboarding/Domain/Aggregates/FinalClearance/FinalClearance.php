<?php

namespace App\Modules\Offboarding\Domain\Aggregates\FinalClearance;

use App\Modules\Offboarding\Domain\Events\FinalClearanceCompleted;

class FinalClearance
{
    private array $recordedEvents = [];

    private function __construct(
        private readonly FinalClearanceId $id,
        private readonly string $planId,
        private readonly string $employeeId,
        private readonly \DateTimeImmutable $clearedAt,
        private readonly string $clearedBy,
        private readonly bool $assetObligationsMet,
        private readonly ?string $payrollNotes,
    ) {}

    public static function create(FinalClearanceId $id, string $planId, string $employeeId, string $clearedBy, bool $assetObligationsMet, ?string $payrollNotes): self
    {
        $c = new self($id, $planId, $employeeId, new \DateTimeImmutable(), $clearedBy, $assetObligationsMet, $payrollNotes);
        $c->recordedEvents[] = new FinalClearanceCompleted($id, $employeeId);
        return $c;
    }

    public static function reconstitute(FinalClearanceId $id, string $planId, string $employeeId, \DateTimeImmutable $clearedAt, string $clearedBy, bool $assetObligationsMet, ?string $payrollNotes): self
    {
        return new self($id, $planId, $employeeId, $clearedAt, $clearedBy, $assetObligationsMet, $payrollNotes);
    }

    public function popRecordedEvents(): array { $e=$this->recordedEvents; $this->recordedEvents=[]; return $e; }
    public function getId(): FinalClearanceId { return $this->id; }
    public function getPlanId(): string { return $this->planId; }
    public function getEmployeeId(): string { return $this->employeeId; }
    public function getClearedAt(): \DateTimeImmutable { return $this->clearedAt; }
    public function getClearedBy(): string { return $this->clearedBy; }
    public function isAssetObligationsMet(): bool { return $this->assetObligationsMet; }
    public function getPayrollNotes(): ?string { return $this->payrollNotes; }
}
