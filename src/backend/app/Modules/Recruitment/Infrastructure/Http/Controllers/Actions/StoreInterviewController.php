<?php

namespace App\Modules\Recruitment\Infrastructure\Http\Controllers\Actions;

use App\Modules\Recruitment\Infrastructure\Http\Controllers\InterviewController;
use Illuminate\Http\Request;

class StoreInterviewController
{
    public function __construct(private InterviewController $controller) {}

    public function __invoke(Request $r)
    {
        return $this->controller->store($r);
    }
}
