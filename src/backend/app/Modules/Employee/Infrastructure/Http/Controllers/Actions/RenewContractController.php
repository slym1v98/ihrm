<?php

namespace App\Modules\Employee\Infrastructure\Http\Controllers\Actions;

use App\Modules\Employee\Infrastructure\Http\Controllers\ContractController;
use Illuminate\Http\Request;

class RenewContractController
{
    public function __construct(private ContractController $controller) {}

    public function __invoke(Request $request, string $id)
    {
        return $this->controller->renew($request, $id);
    }
}
