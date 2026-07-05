<?php

namespace App\Modules\Shift\Infrastructure\Http\Controllers\Actions;

use App\Modules\Shift\Infrastructure\Http\Resources\ShiftAssignmentResource;
use App\Modules\Shift\Infrastructure\Persistence\Eloquent\ShiftAssignmentModel;
use Illuminate\Http\JsonResponse;

class ListShiftAssignmentController
{
    public function __invoke(): JsonResponse
    {
        $assignments = ShiftAssignmentModel::with('shiftTemplate')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => ShiftAssignmentResource::collection($assignments)]);
    }
}
