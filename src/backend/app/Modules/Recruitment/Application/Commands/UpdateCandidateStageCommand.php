<?php

namespace App\Modules\Recruitment\Application\Commands;

readonly class UpdateCandidateStageCommand
{
    public function __construct(public string $id, public string $status) {}
}
