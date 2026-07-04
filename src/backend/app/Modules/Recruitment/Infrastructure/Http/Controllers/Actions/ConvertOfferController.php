<?php

namespace App\Modules\Recruitment\Infrastructure\Http\Controllers\Actions;

use App\Modules\Recruitment\Infrastructure\Http\Controllers\OfferController;
use Illuminate\Http\Request;

class ConvertOfferController
{
    public function __construct(private OfferController $controller) {}

    public function __invoke(Request $r, string $id)
    {
        return $this->controller->convert($r, $id);
    }
}
