<?php

namespace App\Modules\Training\Application\Commands;

class EnrollEmployeeCommand
{
    public function __construct(public readonly string $sessionId, public readonly string $employeeId) {}
}
