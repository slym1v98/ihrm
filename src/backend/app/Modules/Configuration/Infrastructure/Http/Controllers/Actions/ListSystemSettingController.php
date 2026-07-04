<?php

namespace App\Modules\Configuration\Infrastructure\Http\Controllers\Actions;

use App\Modules\Configuration\Domain\Repositories\SystemSettingRepositoryInterface;
use App\Modules\Configuration\Infrastructure\Http\Controllers\SystemSettingController;
use Illuminate\Http\Request;

class ListSystemSettingController
{
    public function __construct(private SystemSettingController $controller) {}

    public function __invoke(Request $request, SystemSettingRepositoryInterface $settings)
    {
        return $this->controller->index($request, $settings);
    }
}
