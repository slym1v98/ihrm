<?php

namespace App\Modules\Configuration\Infrastructure\Http\Controllers\Actions;

use App\Modules\Configuration\Domain\Repositories\LookupRepositoryInterface;
use App\Modules\Configuration\Infrastructure\Http\Controllers\LookupController;

class ShowLookupController
{
    public function __construct(private LookupController $controller) {}

    public function __invoke(string $id, LookupRepositoryInterface $lookups)
    {
        return $this->controller->show($id, $lookups);
    }
}
