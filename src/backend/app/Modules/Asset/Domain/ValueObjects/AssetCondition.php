<?php

namespace App\Modules\Asset\Domain\ValueObjects;

enum AssetCondition: string
{
    case New = 'new';
    case Good = 'good';
    case Fair = 'fair';
    case Poor = 'poor';
    case Damaged = 'damaged';
    case Lost = 'lost';
}
