<?php

namespace App\Modules\Performance\Application\Commands;

class CompleteGoalCommand
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $actualValue = null,
    ) {}
}
