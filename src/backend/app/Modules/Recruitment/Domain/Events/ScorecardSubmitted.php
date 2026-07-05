<?php

namespace App\Modules\Recruitment\Domain\Events;

class ScorecardSubmitted
{
    public function __construct(public readonly array $payload) {}
}
