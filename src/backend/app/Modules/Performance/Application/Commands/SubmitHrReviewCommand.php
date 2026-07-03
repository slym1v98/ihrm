<?php

namespace App\Modules\Performance\Application\Commands;

class SubmitHrReviewCommand
{
    public function __construct(
        public readonly string $id,
        public readonly array $assessment,
    ) {}
}
