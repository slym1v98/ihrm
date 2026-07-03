<?php

namespace App\Modules\Asset\Domain\ValueObjects;

enum AssetItemStatus: string
{
    case Available = 'available';
    case Assigned = 'assigned';
    case Maintenance = 'maintenance';
    case Lost = 'lost';
    case Damaged = 'damaged';

    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Available => in_array($target, [self::Assigned, self::Maintenance, self::Lost, self::Damaged], true),
            self::Assigned => $target === self::Available,
            self::Maintenance => in_array($target, [self::Available, self::Lost, self::Damaged], true),
            self::Lost, self::Damaged => false,
        };
    }
}
