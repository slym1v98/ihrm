<?php

namespace App\Modules\Notification\Infrastructure\Http\Controllers\Actions;

use App\Modules\Notification\Infrastructure\Http\Controllers\MessageTemplateController;
use Illuminate\Http\Request;

class UpdateMessageTemplateController
{
    public function __construct(private MessageTemplateController $controller) {}

    public function __invoke(Request $request, string $id)
    {
        return $this->controller->update($request, $id);
    }
}
