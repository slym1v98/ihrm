<?php

namespace App\Modules\Performance\Application\Commands;

class SubmitSelfReviewCommand
{
    public function __construct(
        public readonly string $id,
        public readonly array $assessment,
    ) {}
}
