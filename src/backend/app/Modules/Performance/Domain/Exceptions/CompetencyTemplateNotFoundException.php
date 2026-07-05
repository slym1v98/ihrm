<?php

namespace App\Modules\Performance\Domain\Exceptions;

class CompetencyTemplateNotFoundException extends \RuntimeException
{
    public function __construct(string $id)
    {
        parent::__construct("CompetencyTemplate not found: {$id}");
    }
}
