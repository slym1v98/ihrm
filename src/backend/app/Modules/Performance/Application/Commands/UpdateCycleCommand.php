<?php

namespace App\Modules\Performance\Application\Commands;

class UpdateCycleCommand
{
    public function __construct(
        public readonly string $id,
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $description,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly array $scoringRules,
    ) {}
}
