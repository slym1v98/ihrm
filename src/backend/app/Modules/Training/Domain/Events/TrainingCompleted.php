<?php

namespace App\Modules\Training\Domain\Events;

class TrainingCompleted
{
    public function __construct(public readonly string $entityId) {}
}
