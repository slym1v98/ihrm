<?php

namespace App\Modules\Recruitment\Infrastructure\Http\Controllers\Actions;

use App\Modules\Recruitment\Infrastructure\Http\Controllers\CandidateController;
use Illuminate\Http\Request;

class UpdateStageCandidateController
{
    public function __construct(private CandidateController $controller) {}

    public function __invoke(Request $r, string $id)
    {
        return $this->controller->updateStage($r, $id);
    }
}
