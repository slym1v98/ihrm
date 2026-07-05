<?php

namespace App\Modules\Employee\Domain\Aggregates\Employee;

use InvalidArgumentException;

final readonly class EmployeeCode
{
    private function __construct(public string $value) {}

    public static function fromString(string $value): self
    {
        $trimmed = trim($value);
        if ($trimmed === '' || strlen($trimmed) > 50) {
            throw new InvalidArgumentException('Employee code must be 1-50 chars.');
        }

        return new self($trimmed);
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
