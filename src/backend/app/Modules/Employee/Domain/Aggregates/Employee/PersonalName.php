<?php

namespace App\Modules\Employee\Domain\Aggregates\Employee;

use InvalidArgumentException;

final readonly class PersonalName
{
    private function __construct(public string $firstName, public string $lastName) {}

    public static function of(string $firstName, string $lastName): self
    {
        $first = trim($firstName);
        $last = trim($lastName);
        if ($first === '' || $last === '') {
            throw new InvalidArgumentException('PersonalName requires first and last.');
        }
        if (strlen($first) > 100 || strlen($last) > 100) {
            throw new InvalidArgumentException('PersonalName parts max 100 chars.');
        }

        return new self($first, $last);
    }

    public function full(): string
    {
        return $this->firstName.' '.$this->lastName;
    }
}
