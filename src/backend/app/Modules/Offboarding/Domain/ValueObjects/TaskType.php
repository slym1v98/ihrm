<?php

namespace App\Modules\Offboarding\Domain\ValueObjects;

enum TaskType: string
{
    case SystemDefined = 'system_defined';
    case Custom = 'custom';
}
