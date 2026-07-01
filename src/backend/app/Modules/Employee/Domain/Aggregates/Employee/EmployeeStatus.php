<?php

namespace App\Modules\Employee\Domain\Aggregates\Employee;

enum EmployeeStatus: string
{
    case Draft = 'draft';
    case Onboarding = 'onboarding';
    case Probation = 'probation';
    case Active = 'active';
    case Suspended = 'suspended';
    case Resigned = 'resigned';
    case Archived = 'archived';
}
