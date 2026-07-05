<?php

namespace App\Modules\Asset\Application\CommandHandlers;

use App\Modules\Asset\Application\Commands\MarkAssetItemStatusCommand;
use App\Modules\Asset\Domain\Aggregates\AssetItem\AssetItem;
use App\Modules\Asset\Domain\Exceptions\AssetItemNotFoundException;
use App\Modules\Asset\Domain\Repositories\AssetAssignmentRepositoryInterface;
use App\Modules\Asset\Domain\Repositories\AssetItemRepositoryInterface;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;

class MarkAssetItemStatusHandler
{
    public function __construct(
        private readonly AssetItemRepositoryInterface $itemRepo,
        private readonly AssetAssignmentRepositoryInterface $assignmentRepo,
    ) {}

    public function handle(MarkAssetItemStatusCommand $command): AssetItem
    {
        $id = AssetItemId::fromString($command->id);
        $item = $this->itemRepo->findById($id);
        if (! $item) {
            throw new AssetItemNotFoundException($command->id);
        }
        $activeAssignment = $this->assignmentRepo->findActiveByAsset($id);
        if ($activeAssignment !== null) {
            throw new \RuntimeException('Cannot change status of an actively assigned asset');
        }
        $item->markStatus($command->newStatus);
        $this->itemRepo->save($item);

        return $item;
    }
}
