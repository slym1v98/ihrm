<?php

namespace App\Modules\Recruitment\Infrastructure\Http\Controllers\Actions;

use App\Modules\Recruitment\Infrastructure\Http\Controllers\OfferController;

class ListOfferController
{
    public function __construct(private OfferController $controller) {}

    public function __invoke()
    {
        return $this->controller->index();
    }
}
