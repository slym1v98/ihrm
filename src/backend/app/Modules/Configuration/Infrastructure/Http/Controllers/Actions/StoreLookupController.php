<?php

namespace App\Modules\Configuration\Infrastructure\Http\Controllers\Actions;

use App\Modules\Configuration\Domain\Repositories\LookupRepositoryInterface;
use App\Modules\Configuration\Infrastructure\Http\Controllers\LookupController;
use App\Modules\Configuration\Infrastructure\Http\Requests\StoreLookupGroupRequest;

class StoreLookupController
{
    public function __construct(private LookupController $controller) {}

    public function __invoke(StoreLookupGroupRequest $request, LookupRepositoryInterface $lookups)
    {
        return $this->controller->store($request, $lookups);
    }
}
