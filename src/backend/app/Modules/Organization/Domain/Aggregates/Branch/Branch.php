<?php

namespace App\Modules\Organization\Domain\Aggregates\Branch;

use App\Modules\Organization\Domain\Events\BranchActivated;
use App\Modules\Organization\Domain\Events\BranchCreated;
use App\Modules\Organization\Domain\Events\BranchDeactivated;
use App\Modules\Organization\Domain\Events\BranchUpdated;
use App\Modules\Organization\Domain\Exceptions\BranchHasActiveDepartmentsException;
use DateTimeImmutable;

final class Branch
{
    /** @var object[] */
    private array $recordedEvents = [];

    private function __construct(
        private readonly BranchId $id,
        private readonly BranchCode $code,
        private BranchName $name,
        private ?string $address,
        private ?string $phone,
        private ?string $email,
        private BranchStatus $status,
    ) {}

    public static function create(
        BranchId $id,
        BranchCode $code,
        BranchName $name,
        ?string $address = null,
        ?string $phone = null,
        ?string $email = null,
    ): self {
        $branch = new self($id, $code, $name, $address, $phone, $email, BranchStatus::Active);
        $branch->record(new BranchCreated($id, $code->value, $name->value, new DateTimeImmutable()));
        return $branch;
    }

    public static function reconstitute(
        BranchId $id,
        BranchCode $code,
        BranchName $name,
        ?string $address,
        ?string $phone,
        ?string $email,
        BranchStatus $status,
    ): self {
        return new self($id, $code, $name, $address, $phone, $email, $status);
    }

    public function update(BranchName $name, ?string $address, ?string $phone, ?string $email): void
    {
        $this->name = $name;
        $this->address = $address;
        $this->phone = $phone;
        $this->email = $email;
        $this->record(new BranchUpdated($this->id, new DateTimeImmutable()));
    }

    public function activate(): void
    {
        if ($this->status->isActive()) {
            return;
        }
        $this->status = BranchStatus::Active;
        $this->record(new BranchActivated($this->id, new DateTimeImmutable()));
    }

    /** @param callable(): bool $hasActiveDepartmentsFn */
    public function deactivate(callable $hasActiveDepartmentsFn): void
    {
        if ($this->status->isInactive()) {
            return;
        }
        if ($hasActiveDepartmentsFn()) {
            throw new BranchHasActiveDepartmentsException($this->id->value);
        }
        $this->status = BranchStatus::Inactive;
        $this->record(new BranchDeactivated($this->id, new DateTimeImmutable()));
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

    public function id(): BranchId { return $this->id; }
    public function code(): BranchCode { return $this->code; }
    public function name(): BranchName { return $this->name; }
    public function address(): ?string { return $this->address; }
    public function phone(): ?string { return $this->phone; }
    public function email(): ?string { return $this->email; }
    public function status(): BranchStatus { return $this->status; }
}
