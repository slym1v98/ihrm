<?php
namespace App\Modules\Asset\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Asset\Application\Queries\GetEmployeeObligationsQuery;
use App\Modules\Asset\Application\QueryHandlers\GetEmployeeObligationsHandler;
use Illuminate\Http\JsonResponse;

class AssetObligationController extends Controller
{
    public function __construct(
        private readonly GetEmployeeObligationsHandler $handler,
    ) {}

    public function __invoke(string $employeeId): JsonResponse
    {
        $obligations = $this->handler->handle(
            new GetEmployeeObligationsQuery(employeeId: $employeeId)
        );
        return response()->json(['data' => $obligations]);
    }
}
