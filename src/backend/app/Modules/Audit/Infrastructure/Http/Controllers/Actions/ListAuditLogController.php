<?php

namespace App\Modules\Audit\Infrastructure\Http\Controllers\Actions;

use App\Modules\Audit\Infrastructure\Http\Controllers\AuditLogController;
use Illuminate\Http\Request;

class ListAuditLogController
{
    public function __construct(private AuditLogController $controller) {}

    public function __invoke(Request $request)
    {
        return $this->controller->index($request);
    }
}
