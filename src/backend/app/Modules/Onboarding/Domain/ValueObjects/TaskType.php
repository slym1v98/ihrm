<?php

namespace App\Modules\Onboarding\Domain\ValueObjects;

enum TaskType: string
{
    case SystemDefined = 'system_defined';
    case Custom = 'custom';
}
