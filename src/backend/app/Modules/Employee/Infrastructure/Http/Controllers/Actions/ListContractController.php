<?php

namespace App\Modules\Employee\Infrastructure\Http\Controllers\Actions;

use App\Modules\Employee\Infrastructure\Http\Controllers\ContractController;
use Illuminate\Http\Request;

class ListContractController
{
    public function __construct(private ContractController $controller) {}

    public function __invoke(Request $request, string $employeeId)
    {
        return $this->controller->index($request, $employeeId);
    }
}
