<?php

namespace App\Modules\Recruitment\Infrastructure\Http\Controllers\Actions;

use App\Modules\Recruitment\Infrastructure\Http\Controllers\CandidateController;
use Illuminate\Http\Request;

class StoreCandidateController
{
    public function __construct(private CandidateController $controller) {}

    public function __invoke(Request $r)
    {
        return $this->controller->store($r);
    }
}
