<?php

namespace App\Modules\Performance\Application\Commands;

class CreateReviewCommand
{
    public function __construct(
        public readonly string $cycleId,
        public readonly string $employeeId,
    ) {}
}
