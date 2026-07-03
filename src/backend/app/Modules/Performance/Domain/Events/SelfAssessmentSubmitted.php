<?php

namespace App\Modules\Performance\Domain\Events;

class SelfAssessmentSubmitted
{
    public function __construct(
        public readonly string $reviewId,
        public readonly string $employeeId,
    ) {}
}
