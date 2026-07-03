<?php

namespace App\Modules\Offboarding\Domain\ValueObjects;

enum OffboardingRequestType: string
{
    case Resignation = 'resignation';
    case CompanyInitiated = 'company_initiated';
}
