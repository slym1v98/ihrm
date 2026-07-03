<?php

namespace App\Modules\Performance\Application\Commands;

class CreateGoalCommand
{
    public function __construct(
        public readonly string $cycleId,
        public readonly ?string $employeeId,
        public readonly string $title,
        public readonly ?string $description,
        public readonly float $weight,
        public readonly ?string $targetValue,
        public readonly int $sortOrder = 0,
    ) {}
}
