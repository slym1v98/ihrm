<?php

namespace App\Modules\Performance\Application\Commands;

class FinalizeReviewCommand
{
    public function __construct(
        public readonly string $id,
        public readonly ?float $finalScore = null,
    ) {}
}
