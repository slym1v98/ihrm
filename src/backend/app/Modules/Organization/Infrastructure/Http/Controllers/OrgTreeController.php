<?php

namespace App\Modules\Organization\Infrastructure\Http\Controllers;

use App\Modules\Organization\Application\Queries\OrgTree\GetOrgTreeQuery;
use App\Modules\Organization\Application\QueryHandlers\OrgTree\GetOrgTreeHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrgTreeController
{
    public function __construct(private GetOrgTreeHandler $handler) {}

    public function __invoke(Request $request): JsonResponse
    {
        $tree = $this->handler->handle(new GetOrgTreeQuery($request->input('branch_id')));
        return response()->json(['data' => $tree]);
    }
}
