<?php

namespace App\Modules\Training\Domain\Events;

class SessionScheduled
{
    public function __construct(public readonly string $entityId) {}
}
