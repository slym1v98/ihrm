<?php

namespace App\Modules\Recruitment\Infrastructure\Http\Controllers\Actions;

use App\Modules\Recruitment\Infrastructure\Http\Controllers\CandidateController;

class ListCandidateController
{
    public function __construct(private CandidateController $controller) {}

    public function __invoke()
    {
        return $this->controller->index();
    }
}
