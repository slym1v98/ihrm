<?php

namespace App\Modules\Asset\Application\CommandHandlers;

use App\Modules\Asset\Application\Commands\AssignAssetCommand;
use App\Modules\Asset\Domain\Aggregates\AssetAssignment\AssetAssignment;
use App\Modules\Asset\Domain\Exceptions\AssetAlreadyAssignedException;
use App\Modules\Asset\Domain\Exceptions\AssetItemNotFoundException;
use App\Modules\Asset\Domain\Repositories\AssetAssignmentRepositoryInterface;
use App\Modules\Asset\Domain\Repositories\AssetItemRepositoryInterface;
use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentId;
use App\Modules\Asset\Domain\ValueObjects\AssetCondition;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;

class AssignAssetHandler
{
    public function __construct(
        private readonly AssetItemRepositoryInterface $itemRepo,
        private readonly AssetAssignmentRepositoryInterface $assignmentRepo,
    ) {}

    public function handle(AssignAssetCommand $command): AssetAssignment
    {
        $assetId = AssetItemId::fromString($command->assetItemId);
        $item = $this->itemRepo->findById($assetId);
        if (! $item) {
            throw new AssetItemNotFoundException($command->assetItemId);
        }
        if ($this->assignmentRepo->findActiveByAsset($assetId)) {
            throw new AssetAlreadyAssignedException($item->getAssetCode());
        }
        $item->assign();
        $assignment = AssetAssignment::create(
            AssetAssignmentId::generate(),
            $assetId,
            $command->employeeId,
            new \DateTimeImmutable,
            $command->expectedReturnAt ? new \DateTimeImmutable($command->expectedReturnAt) : null,
            AssetCondition::from($command->conditionOnIssue ?? $item->getCondition()->value),
        );
        $this->itemRepo->save($item);
        $this->assignmentRepo->save($assignment);

        return $assignment;
    }
}
