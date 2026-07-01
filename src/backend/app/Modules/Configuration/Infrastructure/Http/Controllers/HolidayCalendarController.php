<?php

namespace App\Modules\Configuration\Infrastructure\Http\Controllers;

use App\Modules\Configuration\Domain\Repositories\HolidayCalendarRepositoryInterface;
use App\Modules\Configuration\Infrastructure\Http\Requests\ConfigurationRequest;
use App\Modules\Configuration\Infrastructure\Http\Resources\HolidayCalendarResource;
use App\Modules\Configuration\Infrastructure\Http\Resources\HolidayResource;
use App\Modules\Shared\Http\Resources\PaginatedCollection;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HolidayCalendarController
{
    public function index(Request $request, HolidayCalendarRepositoryInterface $calendars): PaginatedCollection { return new PaginatedCollection($calendars->list((int) $request->integer('per_page', 20)), HolidayCalendarResource::class); }
    public function store(ConfigurationRequest $request, HolidayCalendarRepositoryInterface $calendars): HolidayCalendarResource { return new HolidayCalendarResource($calendars->saveCalendar($request->validated())->load('holidays')); }
    public function storeHoliday(string $id, ConfigurationRequest $request, HolidayCalendarRepositoryInterface $calendars): HolidayResource
    {
        $calendar = $calendars->find($id) ?? throw new NotFoundHttpException('Holiday calendar not found.');
        return new HolidayResource($calendars->saveHoliday($calendar, $request->validated()));
    }
}
