<?php

namespace App\Modules\Configuration\Infrastructure\Http\Controllers;

use App\Modules\Configuration\Domain\Repositories\NotificationThresholdRepositoryInterface;
use App\Modules\Configuration\Infrastructure\Http\Requests\StoreNotificationThresholdRequest;
use App\Modules\Configuration\Infrastructure\Http\Resources\NotificationThresholdResource;
use App\Modules\Shared\Http\Resources\PaginatedCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationThresholdController
{
    public function index(Request $request, NotificationThresholdRepositoryInterface $thresholds): PaginatedCollection
    {
        return new PaginatedCollection($thresholds->list((int) $request->integer('per_page', 20)), NotificationThresholdResource::class);
    }

    public function store(StoreNotificationThresholdRequest $request, NotificationThresholdRepositoryInterface $thresholds): JsonResponse
    {
        return response()->json(['data' => new NotificationThresholdResource($thresholds->save($request->validated()))], 201);
    }
}
