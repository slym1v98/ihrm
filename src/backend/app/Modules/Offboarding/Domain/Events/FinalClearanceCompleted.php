<?php

namespace App\Modules\Offboarding\Domain\Events;

use App\Modules\Offboarding\Domain\Aggregates\FinalClearance\FinalClearanceId;

class FinalClearanceCompleted
{
    public function __construct(
        public readonly FinalClearanceId $clearanceId,
        public readonly string $employeeId,
    ) {}
}
