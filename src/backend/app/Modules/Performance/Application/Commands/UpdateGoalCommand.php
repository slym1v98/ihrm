<?php

namespace App\Modules\Performance\Application\Commands;

class UpdateGoalCommand
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly ?string $description,
        public readonly float $weight,
        public readonly ?string $targetValue,
    ) {}
}
