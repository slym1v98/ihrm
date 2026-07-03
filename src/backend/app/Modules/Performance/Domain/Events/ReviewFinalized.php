<?php

namespace App\Modules\Performance\Domain\Events;

class ReviewFinalized
{
    public function __construct(
        public readonly string $reviewId, public readonly string $employeeId,
    ) {}
}
