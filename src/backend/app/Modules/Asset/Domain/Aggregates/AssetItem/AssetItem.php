<?php

namespace App\Modules\Asset\Domain\Aggregates\AssetItem;

use App\Modules\Asset\Domain\Events\AssetItemCreated;
use App\Modules\Asset\Domain\Events\AssetItemStatusChanged;
use App\Modules\Asset\Domain\Exceptions\AssetStatusTransitionException;
use App\Modules\Asset\Domain\ValueObjects\AssetCondition;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;
use App\Modules\Asset\Domain\ValueObjects\AssetItemStatus;

class AssetItem
{
    private array $recordedEvents = [];

    private function __construct(
        private readonly AssetItemId $id,
        private string $assetCode,
        private string $assetType,
        private string $name,
        private ?string $serialNumber,
        private AssetCondition $condition,
        private AssetItemStatus $status,
        private ?string $notes,
        private ?\DateTimeImmutable $createdAt,
        private ?\DateTimeImmutable $updatedAt,
    ) {}

    public static function create(AssetItemId $id, string $assetCode, string $assetType, string $name, ?string $serialNumber, AssetCondition $condition, ?string $notes = null): self
    {
        $item = new self($id, $assetCode, $assetType, $name, $serialNumber, $condition, AssetItemStatus::Available, $notes, null, null);
        $item->recordedEvents[] = new AssetItemCreated($id);
        return $item;
    }

    public static function reconstitute(AssetItemId $id, string $assetCode, string $assetType, string $name, ?string $serialNumber, AssetCondition $condition, AssetItemStatus $status, ?string $notes, ?\DateTimeImmutable $createdAt = null, ?\DateTimeImmutable $updatedAt = null): self
    {
        return new self($id, $assetCode, $assetType, $name, $serialNumber, $condition, $status, $notes, $createdAt, $updatedAt);
    }

    public function updateDetails(string $assetCode, string $assetType, string $name, ?string $serialNumber, AssetCondition $condition, ?string $notes): void
    {
        $this->assetCode = $assetCode;
        $this->assetType = $assetType;
        $this->name = $name;
        $this->serialNumber = $serialNumber;
        $this->condition = $condition;
        $this->notes = $notes;
    }

    public function markStatus(AssetItemStatus $status): void
    {
        if (!$this->status->canTransitionTo($status)) {
            throw new AssetStatusTransitionException($this->status->value, $status->value);
        }

        $oldStatus = $this->status;
        $this->status = $status;
        $this->recordedEvents[] = new AssetItemStatusChanged($this->id, $oldStatus, $status);
    }

    public function assign(): void
    {
        $this->markStatus(AssetItemStatus::Assigned);
    }

    public function finishReturn(string $conditionOnReturn): AssetItemStatus
    {
        return match ($conditionOnReturn) {
            AssetCondition::Lost->value => AssetItemStatus::Lost,
            AssetCondition::Damaged->value => AssetItemStatus::Damaged,
            AssetCondition::Poor->value => AssetItemStatus::Maintenance,
            default => AssetItemStatus::Available,
        };
    }

    public function getId(): AssetItemId { return $this->id; }
    public function getAssetCode(): string { return $this->assetCode; }
    public function getAssetType(): string { return $this->assetType; }
    public function getName(): string { return $this->name; }
    public function getSerialNumber(): ?string { return $this->serialNumber; }
    public function getCondition(): AssetCondition { return $this->condition; }
    public function getStatus(): AssetItemStatus { return $this->status; }
    public function getNotes(): ?string { return $this->notes; }
    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    public function recordEvent(object $event): void { $this->recordedEvents[] = $event; }
    public function popRecordedEvents(): array { $events = $this->recordedEvents; $this->recordedEvents = []; return $events; }
}
