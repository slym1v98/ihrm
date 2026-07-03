<?php

namespace App\Modules\Asset\Domain\Exceptions;

use RuntimeException;

class AssetHasAssignmentHistoryException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Cannot delete asset with assignment history');
    }
}
