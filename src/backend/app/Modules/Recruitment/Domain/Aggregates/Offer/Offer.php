<?php

namespace App\Modules\Recruitment\Domain\Aggregates\Offer;

use App\Modules\Recruitment\Domain\ValueObjects\OfferStatus;
use Carbon\CarbonImmutable;

class Offer
{
    private function __construct(private readonly OfferId $id, private string $candidateId, private string $requisitionId, private array $terms, private OfferStatus $status, private ?CarbonImmutable $acceptedAt, private ?CarbonImmutable $rejectedAt, private string $createdBy) {}

    public static function create(OfferId $id, string $candidateId, string $requisitionId, array $terms, string $createdBy): self
    {
        return new self($id, $candidateId, $requisitionId, $terms, OfferStatus::Draft, null, null, $createdBy);
    }

    public static function reconstitute(OfferId $id, string $candidateId, string $requisitionId, array $terms, OfferStatus $status, ?CarbonImmutable $acceptedAt, ?CarbonImmutable $rejectedAt, string $createdBy): self
    {
        return new self($id, $candidateId, $requisitionId, $terms, $status, $acceptedAt, $rejectedAt, $createdBy);
    }

    public function send(): void
    {
        if ($this->status !== OfferStatus::Draft) {
            throw new \InvalidArgumentException('Only draft offers can be sent');
        } $this->status = OfferStatus::Sent;
    }

    public function accept(CarbonImmutable $at): void
    {
        if ($this->status->isTerminal()) {
            throw new \InvalidArgumentException('Offer already terminal');
        } $this->status = OfferStatus::Accepted;
        $this->acceptedAt = $at;
    }

    public function reject(CarbonImmutable $at): void
    {
        if ($this->status->isTerminal()) {
            throw new \InvalidArgumentException('Offer already terminal');
        } $this->status = OfferStatus::Rejected;
        $this->rejectedAt = $at;
    }

    public function getId(): OfferId
    {
        return $this->id;
    }

    public function getCandidateId(): string
    {
        return $this->candidateId;
    }

    public function getRequisitionId(): string
    {
        return $this->requisitionId;
    }

    public function getTerms(): array
    {
        return $this->terms;
    }

    public function getStatus(): OfferStatus
    {
        return $this->status;
    }

    public function getAcceptedAt(): ?CarbonImmutable
    {
        return $this->acceptedAt;
    }

    public function getRejectedAt(): ?CarbonImmutable
    {
        return $this->rejectedAt;
    }

    public function getCreatedBy(): string
    {
        return $this->createdBy;
    }
}
