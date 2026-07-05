<?php

namespace App\Modules\Recruitment\Domain\Aggregates\Candidate;

use App\Modules\Recruitment\Domain\ValueObjects\CandidateSource;
use App\Modules\Recruitment\Domain\ValueObjects\CandidateStatus;

class Candidate
{
    private function __construct(private readonly CandidateId $id, private ?string $requisitionId, private ?string $employeeId, private string $fullName, private ?string $email, private ?string $phone, private CandidateSource $source, private ?string $cvFileDescriptor, private CandidateStatus $status, private ?string $notes) {}

    public static function create(CandidateId $id, ?string $requisitionId, string $fullName, ?string $email, ?string $phone, CandidateSource $source, ?string $cvFileDescriptor = null, ?string $notes = null): self
    {
        return new self($id, $requisitionId, null, $fullName, $email, $phone, $source, $cvFileDescriptor, CandidateStatus::New, $notes);
    }

    public static function reconstitute(CandidateId $id, ?string $requisitionId, ?string $employeeId, string $fullName, ?string $email, ?string $phone, CandidateSource $source, ?string $cvFileDescriptor, CandidateStatus $status, ?string $notes): self
    {
        return new self($id, $requisitionId, $employeeId, $fullName, $email, $phone, $source, $cvFileDescriptor, $status, $notes);
    }

    public function moveTo(CandidateStatus $status): void
    {
        if (! $this->status->canTransitionTo($status)) {
            throw new \InvalidArgumentException('Invalid candidate transition');
        } $this->status = $status;
    }

    public function linkEmployee(string $employeeId): void
    {
        if ($this->employeeId !== null) {
            throw new \InvalidArgumentException('Candidate already converted');
        } $this->employeeId = $employeeId;
    }

    public function getId(): CandidateId
    {
        return $this->id;
    }

    public function getRequisitionId(): ?string
    {
        return $this->requisitionId;
    }

    public function getEmployeeId(): ?string
    {
        return $this->employeeId;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getSource(): CandidateSource
    {
        return $this->source;
    }

    public function getCvFileDescriptor(): ?string
    {
        return $this->cvFileDescriptor;
    }

    public function getStatus(): CandidateStatus
    {
        return $this->status;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }
}
