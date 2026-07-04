<?php

namespace App\Modules\Configuration\Infrastructure\Http\Controllers\Actions;

use App\Modules\Configuration\Domain\Repositories\SystemSettingRepositoryInterface;
use App\Modules\Configuration\Infrastructure\Http\Controllers\SystemSettingController;
use App\Modules\Configuration\Infrastructure\Http\Requests\StoreSystemSettingRequest;

class StoreSystemSettingController
{
    public function __construct(private SystemSettingController $controller) {}

    public function __invoke(StoreSystemSettingRequest $request, SystemSettingRepositoryInterface $settings)
    {
        return $this->controller->store($request, $settings);
    }
}
