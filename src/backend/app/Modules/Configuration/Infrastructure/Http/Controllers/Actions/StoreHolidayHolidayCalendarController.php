<?php

namespace App\Modules\Configuration\Infrastructure\Http\Controllers\Actions;

use App\Modules\Configuration\Domain\Repositories\HolidayCalendarRepositoryInterface;
use App\Modules\Configuration\Infrastructure\Http\Controllers\HolidayCalendarController;
use App\Modules\Configuration\Infrastructure\Http\Requests\StoreHolidayRequest;

class StoreHolidayHolidayCalendarController
{
    public function __construct(private HolidayCalendarController $controller) {}

    public function __invoke(string $id, StoreHolidayRequest $request, HolidayCalendarRepositoryInterface $calendars)
    {
        return $this->controller->storeHoliday($id, $request, $calendars);
    }
}
