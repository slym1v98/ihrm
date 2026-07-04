<?php

namespace App\Modules\Recruitment\Infrastructure\Http\Controllers\Actions;

use App\Modules\Recruitment\Infrastructure\Http\Controllers\InterviewController;

class ListInterviewController
{
    public function __construct(private InterviewController $controller) {}

    public function __invoke()
    {
        return $this->controller->index();
    }
}
