<?php
namespace App\Modules\Asset\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Asset\Application\Commands\CreateAssetItemCommand;
use App\Modules\Asset\Application\Commands\UpdateAssetItemCommand;
use App\Modules\Asset\Application\Commands\MarkAssetItemStatusCommand;
use App\Modules\Asset\Application\CommandHandlers\CreateAssetItemHandler;
use App\Modules\Asset\Application\CommandHandlers\UpdateAssetItemHandler;
use App\Modules\Asset\Application\CommandHandlers\MarkAssetItemStatusHandler;
use App\Modules\Asset\Application\Queries\ListAssetItemsQuery;
use App\Modules\Asset\Application\QueryHandlers\ListAssetItemsHandler;
use App\Modules\Asset\Domain\Repositories\AssetItemRepositoryInterface;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;
use App\Modules\Asset\Domain\ValueObjects\AssetItemStatus;
use App\Modules\Asset\Domain\Exceptions\AssetItemNotFoundException;
use App\Modules\Asset\Domain\Exceptions\AssetHasAssignmentHistoryException;
use App\Modules\Asset\Domain\Repositories\AssetAssignmentRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetItemController extends Controller
{
    public function __construct(
        private readonly CreateAssetItemHandler $createHandler,
        private readonly UpdateAssetItemHandler $updateHandler,
        private readonly MarkAssetItemStatusHandler $markStatusHandler,
        private readonly ListAssetItemsHandler $listHandler,
        private readonly AssetItemRepositoryInterface $itemRepo,
        private readonly AssetAssignmentRepositoryInterface $assignmentRepo,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $items = $this->listHandler->handle(
            new ListAssetItemsQuery(
                status: $request->query('status'),
                assetType: $request->query('asset_type'),
            )
        );
        return response()->json(['data' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'asset_code' => 'required|string|max:255',
            'asset_type' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'condition' => 'string',
            'notes' => 'nullable|string',
        ]);
        $item = $this->createHandler->handle(
            new CreateAssetItemCommand(
                assetCode: $validated['asset_code'],
                assetType: $validated['asset_type'],
                name: $validated['name'],
                serialNumber: $validated['serial_number'] ?? null,
                condition: $validated['condition'] ?? 'new',
                notes: $validated['notes'] ?? null,
            )
        );
        return response()->json(['data' => []] , 201);
    }

    public function show(string $id): JsonResponse
    {
        $item = $this->itemRepo->findById(AssetItemId::fromString($id));
        if (!$item) {
            throw new AssetItemNotFoundException($id);
        }
        return response()->json(['data' => []]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'asset_type' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'condition' => 'string',
            'notes' => 'nullable|string',
        ]);
        $this->updateHandler->handle(
            new UpdateAssetItemCommand(
                id: $id,
                assetType: $validated['asset_type'],
                name: $validated['name'],
                serialNumber: $validated['serial_number'] ?? null,
                condition: $validated['condition'] ?? 'good',
                notes: $validated['notes'] ?? null,
            )
        );
        return response()->json(['message' => 'Updated']);
    }

    public function destroy(string $id): JsonResponse
    {
        $itemId = AssetItemId::fromString($id);
        $item = $this->itemRepo->findById($itemId);
        if (!$item) {
            throw new AssetItemNotFoundException($id);
        }
        $assignments = $this->assignmentRepo->all(['asset_item_id' => $id]);
        if (count($assignments) > 0) {
            throw new AssetHasAssignmentHistoryException();
        }
        $this->itemRepo->delete($item);
        return response()->json(['message' => 'Asset item deleted']);
    }

    public function markAvailable(string $id): JsonResponse
    {
        return $this->markStatus($id, AssetItemStatus::Available);
    }

    public function markMaintenance(string $id): JsonResponse
    {
        return $this->markStatus($id, AssetItemStatus::Maintenance);
    }

    public function markLost(string $id): JsonResponse
    {
        return $this->markStatus($id, AssetItemStatus::Lost);
    }

    public function markDamaged(string $id): JsonResponse
    {
        return $this->markStatus($id, AssetItemStatus::Damaged);
    }

    private function markStatus(string $id, AssetItemStatus $status): JsonResponse
    {
        $this->markStatusHandler->handle(
            new MarkAssetItemStatusCommand(id: $id, newStatus: $status)
        );
        return response()->json(['message' => 'Status updated']);
    }
}
