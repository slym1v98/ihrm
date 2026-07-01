<?php

namespace App\Modules\Employee\Domain\Aggregates\Employee;

final readonly class Address
{
    public function __construct(
        public ?string $street = null,
        public ?string $city = null,
        public ?string $province = null,
        public ?string $postalCode = null,
        public ?string $country = null,
    ) {
    }

    public function isEmpty(): bool
    {
        return $this->street === null
            && $this->city === null
            && $this->province === null
            && $this->postalCode === null
            && $this->country === null;
    }
}
