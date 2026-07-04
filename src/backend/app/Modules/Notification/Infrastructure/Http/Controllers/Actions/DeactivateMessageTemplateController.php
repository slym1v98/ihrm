<?php

namespace App\Modules\Notification\Infrastructure\Http\Controllers\Actions;

use App\Modules\Notification\Infrastructure\Http\Controllers\MessageTemplateController;

class DeactivateMessageTemplateController
{
    public function __construct(private MessageTemplateController $controller) {}

    public function __invoke(string $id)
    {
        return $this->controller->deactivate($id);
    }
}
