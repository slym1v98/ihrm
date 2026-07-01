<?php

namespace App\Modules\Employee\Domain\Aggregates\EmployeeDocument;

use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

final readonly class EmployeeDocumentId
{
    private function __construct(public string $value)
    {
    }

    public static function fromString(string $value): self
    {
        if (! Uuid::isValid($value)) {
            throw new InvalidArgumentException("Invalid EmployeeDocumentId: {$value}");
        }

        return new self($value);
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
