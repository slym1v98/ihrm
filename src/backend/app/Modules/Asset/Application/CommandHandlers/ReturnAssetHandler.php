<?php

namespace App\Modules\Asset\Application\CommandHandlers;

use App\Modules\Asset\Application\Commands\ReturnAssetCommand;
use App\Modules\Asset\Domain\Aggregates\AssetReturn\AssetReturn;
use App\Modules\Asset\Domain\Exceptions\AssetAssignmentNotFoundException;
use App\Modules\Asset\Domain\Exceptions\AssetItemNotFoundException;
use App\Modules\Asset\Domain\Repositories\AssetAssignmentRepositoryInterface;
use App\Modules\Asset\Domain\Repositories\AssetItemRepositoryInterface;
use App\Modules\Asset\Domain\Repositories\AssetReturnRepositoryInterface;
use App\Modules\Asset\Domain\ValueObjects\AssetAssignmentId;
use App\Modules\Asset\Domain\ValueObjects\AssetCondition;
use App\Modules\Asset\Domain\ValueObjects\AssetReturnId;

class ReturnAssetHandler
{
    public function __construct(
        private readonly AssetAssignmentRepositoryInterface $assignmentRepo,
        private readonly AssetItemRepositoryInterface $itemRepo,
        private readonly AssetReturnRepositoryInterface $returnRepo,
    ) {}

    public function handle(ReturnAssetCommand $command): AssetReturn
    {
        $assignmentId = AssetAssignmentId::fromString($command->assignmentId);
        $assignment = $this->assignmentRepo->findById($assignmentId);
        if (! $assignment) {
            throw new AssetAssignmentNotFoundException($command->assignmentId);
        }
        $assignment->completeReturn();
        $return = AssetReturn::create(
            AssetReturnId::generate(),
            $assignmentId,
            new \DateTimeImmutable,
            AssetCondition::from($command->conditionOnReturn),
            $command->notes,
            $command->settlementAmount,
        );
        $itemId = $assignment->getAssetItemId();
        $item = $this->itemRepo->findById($itemId);
        if (! $item) {
            throw new AssetItemNotFoundException($itemId->value);
        }
        $newItemStatus = $item->finishReturn($command->conditionOnReturn);
        $item->markStatus($newItemStatus);
        $this->assignmentRepo->save($assignment);
        $this->returnRepo->save($return);
        $this->itemRepo->save($item);

        return $return;
    }
}
