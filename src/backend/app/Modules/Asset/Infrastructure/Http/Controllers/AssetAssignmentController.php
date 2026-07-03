<?php
namespace App\Modules\Asset\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Asset\Application\Commands\AssignAssetCommand;
use App\Modules\Asset\Application\Commands\ReturnAssetCommand;
use App\Modules\Asset\Application\CommandHandlers\AssignAssetHandler;
use App\Modules\Asset\Application\CommandHandlers\ReturnAssetHandler;
use App\Modules\Asset\Application\Queries\ListAssetAssignmentsQuery;
use App\Modules\Asset\Application\QueryHandlers\ListAssetAssignmentsHandler;
use App\Modules\Asset\Domain\Repositories\AssetAssignmentRepositoryInterface;
use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentId;
use App\Modules\Asset\Domain\Exceptions\AssetAssignmentNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetAssignmentController extends Controller
{
    public function __construct(
        private readonly AssignAssetHandler $assignHandler,
        private readonly ReturnAssetHandler $returnHandler,
        private readonly ListAssetAssignmentsHandler $listHandler,
        private readonly AssetAssignmentRepositoryInterface $assignmentRepo,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $assignments = $this->listHandler->handle(
            new ListAssetAssignmentsQuery(
                employeeId: $request->query('employee_id'),
                assetItemId: $request->query('asset_item_id'),
                status: $request->query('status'),
            )
        );
        return response()->json(['data' => $assignments]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'asset_item_id' => 'required|uuid',
            'employee_id' => 'required|uuid',
            'expected_return_at' => 'nullable|date',
            'condition_on_issue' => 'nullable|string',
        ]);
        $assignment = $this->assignHandler->handle(
            new AssignAssetCommand(
                assetItemId: $validated['asset_item_id'],
                employeeId: $validated['employee_id'],
                expectedReturnAt: $validated['expected_return_at'] ?? null,
                conditionOnIssue: $validated['condition_on_issue'] ?? null,
            )
        );
        return response()->json(['data' => []], 201);
    }

    public function show(string $id): JsonResponse
    {
        $assignment = $this->assignmentRepo->findById(AssetAssignmentId::fromString($id));
        if (!$assignment) {
            throw new AssetAssignmentNotFoundException($id);
        }
        return response()->json(['data' => []]);
    }

    public function returnAsset(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'condition_on_return' => 'required|string',
            'notes' => 'nullable|string',
            'settlement_amount' => 'nullable|numeric|min:0',
        ]);
        $this->returnHandler->handle(
            new ReturnAssetCommand(
                assignmentId: $id,
                conditionOnReturn: $validated['condition_on_return'],
                notes: $validated['notes'] ?? null,
                settlementAmount: (float)($validated['settlement_amount'] ?? 0),
            )
        );
        return response()->json(['message' => 'Asset returned'], 201);
    }
}
