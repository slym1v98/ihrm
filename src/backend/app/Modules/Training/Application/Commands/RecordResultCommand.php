<?php

namespace App\Modules\Training\Application\Commands;

class RecordResultCommand
{
    public function __construct(public readonly string $enrollmentId, public readonly ?float $score = null, public readonly ?bool $passed = null, public readonly ?string $certificateCode = null, public readonly ?string $notes = null) {}
}
