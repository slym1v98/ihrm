<?php

namespace App\Modules\Notification\Infrastructure\Http\Controllers\Actions;

use App\Modules\Notification\Infrastructure\Http\Controllers\MessageTemplateController;
use Illuminate\Http\Request;

class StoreMessageTemplateController
{
    public function __construct(private MessageTemplateController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->store($request);
    }
}
