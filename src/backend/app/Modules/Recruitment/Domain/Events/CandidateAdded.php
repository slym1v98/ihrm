<?php

namespace App\Modules\Recruitment\Domain\Events;

class CandidateAdded
{
    public function __construct(public readonly array $payload) {}
}
