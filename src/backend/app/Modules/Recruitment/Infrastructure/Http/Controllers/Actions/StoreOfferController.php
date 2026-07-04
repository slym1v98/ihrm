<?php

namespace App\Modules\Recruitment\Infrastructure\Http\Controllers\Actions;

use App\Modules\Recruitment\Infrastructure\Http\Controllers\OfferController;
use Illuminate\Http\Request;

class StoreOfferController
{
    public function __construct(private OfferController $controller) {}

    public function __invoke(Request $r)
    {
        return $this->controller->store($r);
    }
}
