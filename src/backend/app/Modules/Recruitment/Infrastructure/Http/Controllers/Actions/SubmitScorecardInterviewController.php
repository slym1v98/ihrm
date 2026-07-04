<?php

namespace App\Modules\Recruitment\Infrastructure\Http\Controllers\Actions;

use App\Modules\Recruitment\Infrastructure\Http\Controllers\InterviewController;
use Illuminate\Http\Request;

class SubmitScorecardInterviewController
{
    public function __construct(private InterviewController $controller) {}

    public function __invoke(Request $r, string $id)
    {
        return $this->controller->submitScorecard($r, $id);
    }
}
