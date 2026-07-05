<?php

namespace App\Modules\Recruitment\Application\Commands;

readonly class SubmitRequisitionCommand
{
    public function __construct(public string $id, public string $submittedBy) {}
}
