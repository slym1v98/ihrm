<?php

namespace App\Modules\Configuration\Infrastructure\Http\Controllers\Actions;

use App\Modules\Configuration\Domain\Repositories\HolidayCalendarRepositoryInterface;
use App\Modules\Configuration\Infrastructure\Http\Controllers\HolidayCalendarController;
use Illuminate\Http\Request;

class ListHolidayCalendarController
{
    public function __construct(private HolidayCalendarController $controller) {}

    public function __invoke(Request $request, HolidayCalendarRepositoryInterface $calendars)
    {
        return $this->controller->index($request, $calendars);
    }
}
