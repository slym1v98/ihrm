<?php

namespace App\Modules\Recruitment\Domain\Events;

class CandidateHired
{
    public function __construct(public readonly array $payload) {}
}
