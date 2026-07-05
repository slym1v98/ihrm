<?php

namespace App\Modules\Configuration\Infrastructure\Persistence\Repositories;

use App\Modules\Configuration\Domain\Repositories\HolidayCalendarRepositoryInterface;
use App\Modules\Configuration\Infrastructure\Persistence\Eloquent\HolidayCalendarModel;
use App\Modules\Configuration\Infrastructure\Persistence\Eloquent\HolidayModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentHolidayCalendarRepository implements HolidayCalendarRepositoryInterface
{
    public function list(int $perPage = 20): LengthAwarePaginator
    {
        return HolidayCalendarModel::with('holidays')->orderByDesc('year')->paginate($perPage);
    }

    public function find(string $id): ?HolidayCalendarModel
    {
        return HolidayCalendarModel::with('holidays')->find($id);
    }

    public function saveCalendar(array $attributes): HolidayCalendarModel
    {
        return HolidayCalendarModel::updateOrCreate(['id' => $attributes['id'] ?? null], $attributes);
    }

    public function saveHoliday(HolidayCalendarModel $calendar, array $attributes): HolidayModel
    {
        return $calendar->holidays()->updateOrCreate(['id' => $attributes['id'] ?? null], $attributes);
    }
}
