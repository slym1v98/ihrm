<?php

namespace App\Modules\Configuration\Infrastructure\Http\Controllers\Actions;

use App\Modules\Configuration\Domain\Repositories\LookupRepositoryInterface;
use App\Modules\Configuration\Infrastructure\Http\Controllers\LookupController;
use Illuminate\Http\Request;

class ListLookupController
{
    public function __construct(private LookupController $controller) {}

    public function __invoke(Request $request, LookupRepositoryInterface $lookups)
    {
        return $this->controller->index($request, $lookups);
    }
}
