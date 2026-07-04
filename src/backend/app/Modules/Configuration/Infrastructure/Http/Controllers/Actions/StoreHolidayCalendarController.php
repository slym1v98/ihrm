<?php

namespace App\Modules\Configuration\Infrastructure\Http\Controllers\Actions;

use App\Modules\Configuration\Domain\Repositories\HolidayCalendarRepositoryInterface;
use App\Modules\Configuration\Infrastructure\Http\Controllers\HolidayCalendarController;
use App\Modules\Configuration\Infrastructure\Http\Requests\StoreHolidayCalendarRequest;

class StoreHolidayCalendarController
{
    public function __construct(private HolidayCalendarController $controller) {}

    public function __invoke(StoreHolidayCalendarRequest $request, HolidayCalendarRepositoryInterface $calendars)
    {
        return $this->controller->store($request, $calendars);
    }
}
