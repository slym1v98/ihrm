<?php

namespace App\Modules\Offboarding\Application\Commands;

class CompleteFinalClearanceCommand
{
    public function __construct(
        public readonly string $planId,
        public readonly string $clearedBy,
        public readonly ?string $payrollNotes = null,
    ) {}
}
