<?php

namespace App\Modules\Recruitment\Application\Commands;

readonly class CreateRequisitionCommand
{
    public function __construct(public string $departmentId, public string $position, public int $headcount, public string $reason, public string $createdBy) {}
}
