<?php

namespace App\Modules\Asset\Domain\Aggregates\AssetReturn;

use App\Modules\Asset\Domain\Events\AssetReturned;
use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentId;
use App\Modules\Asset\Domain\ValueObjects\AssetCondition;
use App\Modules\Asset\Domain\ValueObjects\AssetReturnId;

class AssetReturn
{
    private array $recordedEvents = [];

    private function __construct(
        private readonly AssetReturnId $id,
        private readonly AssetAssignmentId $assetAssignmentId,
        private \DateTimeImmutable $returnedAt,
        private AssetCondition $conditionOnReturn,
        private ?string $notes,
        private float $settlementAmount,
        private ?\DateTimeImmutable $createdAt,
        private ?\DateTimeImmutable $updatedAt,
    ) {}

    public static function create(AssetReturnId $id, AssetAssignmentId $assetAssignmentId, \DateTimeImmutable $returnedAt, AssetCondition $conditionOnReturn, ?string $notes, float $settlementAmount = 0.0): self
    {
        $return = new self($id, $assetAssignmentId, $returnedAt, $conditionOnReturn, $notes, $settlementAmount, null, null);
        $return->recordedEvents[] = new AssetReturned($id, $assetAssignmentId);
        return $return;
    }

    public static function reconstitute(AssetReturnId $id, AssetAssignmentId $assetAssignmentId, \DateTimeImmutable $returnedAt, AssetCondition $conditionOnReturn, ?string $notes, float $settlementAmount, ?\DateTimeImmutable $createdAt = null, ?\DateTimeImmutable $updatedAt = null): self
    {
        return new self($id, $assetAssignmentId, $returnedAt, $conditionOnReturn, $notes, $settlementAmount, $createdAt, $updatedAt);
    }

    public function getId(): AssetReturnId { return $this->id; }
    public function getAssetAssignmentId(): AssetAssignmentId { return $this->assetAssignmentId; }
    public function getReturnedAt(): \DateTimeImmutable { return $this->returnedAt; }
    public function getConditionOnReturn(): AssetCondition { return $this->conditionOnReturn; }
    public function getNotes(): ?string { return $this->notes; }
    public function getSettlementAmount(): float { return $this->settlementAmount; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }


    public function toArray(): array
    {
        return [
            'id' => $this->id->value,
            'asset_assignment_id' => $this->assetAssignmentId->value,
            'returned_at' => $this->returnedAt->format('c'),
            'condition_on_return' => $this->conditionOnReturn->value,
            'notes' => $this->notes,
            'settlement_amount' => $this->settlementAmount,
        ];
    }

    public function recordEvent(object $event): void { $this->recordedEvents[] = $event; }
    public function popRecordedEvents(): array { $events = $this->recordedEvents; $this->recordedEvents = []; return $events; }
}
