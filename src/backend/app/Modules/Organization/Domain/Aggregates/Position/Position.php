<?php

namespace App\Modules\Organization\Domain\Aggregates\Position;

use App\Modules\Organization\Domain\Events\PositionActivated;
use App\Modules\Organization\Domain\Events\PositionCreated;
use App\Modules\Organization\Domain\Events\PositionDeactivated;
use App\Modules\Organization\Domain\Events\PositionUpdated;
use DateTimeImmutable;

final class Position
{
    /** @var object[] */
    private array $recordedEvents = [];

    private function __construct(
        private readonly PositionId $id,
        private readonly PositionCode $code,
        private PositionName $name,
        private ?int $level,
        private ?string $description,
        private PositionStatus $status,
    ) {}

    public static function create(
        PositionId $id,
        PositionCode $code,
        PositionName $name,
        ?int $level = null,
        ?string $description = null,
    ): self {
        $position = new self($id, $code, $name, $level, $description, PositionStatus::Active);
        $position->record(new PositionCreated($id, $code->value, $name->value, new DateTimeImmutable()));
        return $position;
    }

    public static function reconstitute(
        PositionId $id,
        PositionCode $code,
        PositionName $name,
        ?int $level,
        ?string $description,
        PositionStatus $status,
    ): self {
        return new self($id, $code, $name, $level, $description, $status);
    }

    public function update(PositionName $name, ?int $level, ?string $description): void
    {
        $this->name = $name;
        $this->level = $level;
        $this->description = $description;
        $this->record(new PositionUpdated($this->id, new DateTimeImmutable()));
    }

    public function activate(): void
    {
        if ($this->status->isActive()) {
            return;
        }
        $this->status = PositionStatus::Active;
        $this->record(new PositionActivated($this->id, new DateTimeImmutable()));
    }

    public function deactivate(): void
    {
        if ($this->status->isInactive()) {
            return;
        }
        $this->status = PositionStatus::Inactive;
        $this->record(new PositionDeactivated($this->id, new DateTimeImmutable()));
    }

    private function record(object $event): void
    {
        $this->recordedEvents[] = $event;
    }

    /** @return object[] */
    public function releaseEvents(): array
    {
        $events = $this->recordedEvents;
        $this->recordedEvents = [];
        return $events;
    }

    public function id(): PositionId { return $this->id; }
    public function code(): PositionCode { return $this->code; }
    public function name(): PositionName { return $this->name; }
    public function level(): ?int { return $this->level; }
    public function description(): ?string { return $this->description; }
    public function status(): PositionStatus { return $this->status; }
}
