<?php

namespace App\Modules\Employee\Domain\Aggregates\Contract;

enum ContractStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Expired = 'expired';
    case Terminated = 'terminated';
    case Cancelled = 'cancelled';
}
