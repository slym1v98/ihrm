<?php

namespace App\Modules\Configuration\Infrastructure\Http\Controllers;

use App\Modules\Configuration\Domain\Repositories\SystemSettingRepositoryInterface;
use App\Modules\Configuration\Infrastructure\Http\Requests\StoreSystemSettingRequest;
use App\Modules\Configuration\Infrastructure\Http\Resources\SystemSettingResource;
use App\Modules\Shared\Http\Resources\PaginatedCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemSettingController
{
    public function index(Request $request, SystemSettingRepositoryInterface $settings): PaginatedCollection { return new PaginatedCollection($settings->list((int) $request->integer('per_page', 50)), SystemSettingResource::class); }
    public function store(StoreSystemSettingRequest $request, SystemSettingRepositoryInterface $settings): JsonResponse { return response()->json(['data' => new SystemSettingResource($settings->save($request->validated()))], 201); }
}
