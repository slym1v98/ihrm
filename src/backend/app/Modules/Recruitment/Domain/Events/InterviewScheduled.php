<?php

namespace App\Modules\Recruitment\Domain\Events;

class InterviewScheduled
{
    public function __construct(public readonly array $payload) {}
}
