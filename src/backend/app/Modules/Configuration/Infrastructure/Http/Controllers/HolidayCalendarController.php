<?php

namespace App\Modules\Configuration\Infrastructure\Http\Controllers;

use App\Modules\Configuration\Domain\Repositories\HolidayCalendarRepositoryInterface;
use App\Modules\Configuration\Infrastructure\Http\Requests\StoreHolidayCalendarRequest;
use App\Modules\Configuration\Infrastructure\Http\Requests\StoreHolidayRequest;
use App\Modules\Configuration\Infrastructure\Http\Resources\HolidayCalendarResource;
use App\Modules\Configuration\Infrastructure\Http\Resources\HolidayResource;
use App\Modules\Shared\Http\Resources\PaginatedCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class HolidayCalendarController
{
    public function index(Request $request, HolidayCalendarRepositoryInterface $calendars): PaginatedCollection
    {
        return new PaginatedCollection($calendars->list((int) $request->integer('per_page', 20)), HolidayCalendarResource::class);
    }

    public function store(StoreHolidayCalendarRequest $request, HolidayCalendarRepositoryInterface $calendars): JsonResponse
    {
        return response()->json(['data' => new HolidayCalendarResource($calendars->saveCalendar($request->validated())->load('holidays'))], 201);
    }

    public function storeHoliday(string $id, StoreHolidayRequest $request, HolidayCalendarRepositoryInterface $calendars): JsonResponse
    {
        $calendar = $calendars->find($id) ?? throw new NotFoundHttpException('Holiday calendar not found.');

        return response()->json(['data' => new HolidayResource($calendars->saveHoliday($calendar, $request->validated()))], 201);
    }
}
