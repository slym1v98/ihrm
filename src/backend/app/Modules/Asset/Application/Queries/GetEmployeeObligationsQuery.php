<?php
namespace App\Modules\Asset\Application\Queries;

class GetEmployeeObligationsQuery
{
    public function __construct(
        public readonly string $employeeId,
    ) {}
}
