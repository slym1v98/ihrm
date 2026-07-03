<?php

namespace App\Modules\Onboarding\Domain\ValueObjects;

enum OwnerType: string
{
    case Department = 'department';
    case UserRole = 'user_role';
}
