<?php

namespace App\Modules\Employee\Domain\Aggregates\Contract;

final readonly class ContractTerm
{
    public function __construct(
        public string $type,
        public DateRange $dateRange,
        public ?float $salary = null,
    ) {}
}
