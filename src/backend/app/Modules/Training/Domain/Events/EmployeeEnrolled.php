<?php

namespace App\Modules\Training\Domain\Events;

class EmployeeEnrolled
{
    public function __construct(public readonly string $entityId) {}
}
