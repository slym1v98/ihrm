<?php

namespace App\Modules\Recruitment\Domain\Exceptions;

use App\Modules\Shared\Exceptions\AppException;

class OfferNotFoundException extends AppException
{
    public function __construct(string $d = '')
    {
        parent::__construct('OFFER_NOT_FOUND', trim('Offer not found: '.$d));
    }

    public function getHttpStatus(): int
    {
        return 404;
    }
}
