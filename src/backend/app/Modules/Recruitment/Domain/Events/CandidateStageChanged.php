<?php

namespace App\Modules\Recruitment\Domain\Events;

class CandidateStageChanged
{
    public function __construct(public readonly array $payload) {}
}
