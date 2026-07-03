<?php

namespace App\Modules\Asset\Domain\ValueObjects;

enum AssetAssignmentStatus: string
{
    case Active = 'active';
    case Returned = 'returned';
}
