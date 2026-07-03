<?php

namespace App\Modules\Offboarding\Domain\Exceptions;

class MandatoryTaskIncompleteException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('All mandatory tasks must be completed or waived before completing the plan');
    }
}
