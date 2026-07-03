<?php

namespace App\Modules\Offboarding\Domain\ValueObjects;

enum OwnerType: string
{
    case Department = 'department';
    case UserRole = 'user_role';
}
