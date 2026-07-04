<?php

namespace App\Modules\Configuration\Infrastructure\Http\Controllers\Actions;

use App\Modules\Configuration\Domain\Repositories\LookupRepositoryInterface;
use App\Modules\Configuration\Infrastructure\Http\Controllers\LookupController;
use App\Modules\Configuration\Infrastructure\Http\Requests\StoreLookupValueRequest;

class StoreValueLookupController
{
    public function __construct(private LookupController $controller) {}

    public function __invoke(string $id, StoreLookupValueRequest $request, LookupRepositoryInterface $lookups)
    {
        return $this->controller->storeValue($id, $request, $lookups);
    }
}
