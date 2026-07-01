<?php

namespace App\Modules\Employee\Domain\Aggregates\EmployeeDocument;

enum DocumentStatus: string
{
    case Active = 'active';
    case Archived = 'archived';
    case Expired = 'expired';
}
