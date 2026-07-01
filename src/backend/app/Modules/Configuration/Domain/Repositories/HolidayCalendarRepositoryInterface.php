<?php

namespace App\Modules\Configuration\Domain\Repositories;

use App\Modules\Configuration\Infrastructure\Persistence\Eloquent\HolidayCalendarModel;
use App\Modules\Configuration\Infrastructure\Persistence\Eloquent\HolidayModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface HolidayCalendarRepositoryInterface
{
    public function list(int $perPage = 20): LengthAwarePaginator;
    public function find(string $id): ?HolidayCalendarModel;
    public function saveCalendar(array $attributes): HolidayCalendarModel;
    public function saveHoliday(HolidayCalendarModel $calendar, array $attributes): HolidayModel;
}
