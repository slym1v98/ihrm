<?php

namespace App\Modules\Asset\Domain\Aggregates\AssetAssignment;

use App\Modules\Asset\Domain\Events\AssetAssigned;
use App\Modules\Asset\Domain\Exceptions\AssetAssignmentAlreadyReturnedException;
use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentId;
use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentStatus;
use App\Modules\Asset\Domain\ValueObjects\AssetCondition;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;

class AssetAssignment
{
    private array $recordedEvents = [];

    private function __construct(
        private readonly AssetAssignmentId $id,
        private readonly AssetItemId $assetItemId,
        private readonly string $employeeId,
        private \DateTimeImmutable $issuedAt,
        private ?\DateTimeImmutable $expectedReturnAt,
        private AssetCondition $conditionOnIssue,
        private AssetAssignmentStatus $status,
        private ?\DateTimeImmutable $createdAt,
        private ?\DateTimeImmutable $updatedAt,
    ) {}

    public static function create(AssetAssignmentId $id, AssetItemId $assetItemId, string $employeeId, \DateTimeImmutable $issuedAt, ?\DateTimeImmutable $expectedReturnAt, AssetCondition $conditionOnIssue): self
    {
        $assignment = new self($id, $assetItemId, $employeeId, $issuedAt, $expectedReturnAt, $conditionOnIssue, AssetAssignmentStatus::Active, null, null);
        $assignment->recordedEvents[] = new AssetAssigned($id, $assetItemId, $employeeId);

        return $assignment;
    }

    public static function reconstitute(AssetAssignmentId $id, AssetItemId $assetItemId, string $employeeId, \DateTimeImmutable $issuedAt, ?\DateTimeImmutable $expectedReturnAt, AssetCondition $conditionOnIssue, AssetAssignmentStatus $status, ?\DateTimeImmutable $createdAt = null, ?\DateTimeImmutable $updatedAt = null): self
    {
        return new self($id, $assetItemId, $employeeId, $issuedAt, $expectedReturnAt, $conditionOnIssue, $status, $createdAt, $updatedAt);
    }

    public function completeReturn(): void
    {
        if ($this->status !== AssetAssignmentStatus::Active) {
            throw new AssetAssignmentAlreadyReturnedException($this->id->value);
        }

        $this->status = AssetAssignmentStatus::Returned;
    }

    public function getId(): AssetAssignmentId
    {
        return $this->id;
    }

    public function getAssetItemId(): AssetItemId
    {
        return $this->assetItemId;
    }

    public function getEmployeeId(): string
    {
        return $this->employeeId;
    }

    public function getIssuedAt(): \DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function getExpectedReturnAt(): ?\DateTimeImmutable
    {
        return $this->expectedReturnAt;
    }

    public function getConditionOnIssue(): AssetCondition
    {
        return $this->conditionOnIssue;
    }

    public function getStatus(): AssetAssignmentStatus
    {
        return $this->status;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id->value,
            'asset_item_id' => $this->assetItemId->value,
            'employee_id' => $this->employeeId,
            'issued_at' => $this->issuedAt->format('c'),
            'expected_return_at' => $this->expectedReturnAt?->format('c'),
            'condition_on_issue' => $this->conditionOnIssue->value,
            'status' => $this->status->value,
            'created_at' => $this->createdAt?->format('c'),
            'updated_at' => $this->updatedAt?->format('c'),
        ];
    }

    public function recordEvent(object $event): void
    {
        $this->recordedEvents[] = $event;
    }

    public function popRecordedEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];

        return $events;
    }
}
