<?php

namespace App\Modules\Asset\Application\CommandHandlers;

use App\Modules\Asset\Application\Commands\UpdateAssetItemCommand;
use App\Modules\Asset\Domain\Exceptions\AssetItemNotFoundException;
use App\Modules\Asset\Domain\Repositories\AssetItemRepositoryInterface;
use App\Modules\Asset\Domain\ValueObjects\AssetCondition;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;

class UpdateAssetItemHandler
{
    public function __construct(
        private readonly AssetItemRepositoryInterface $repo,
    ) {}

    public function handle(UpdateAssetItemCommand $command): AssetItem
    {
        $id = AssetItemId::fromString($command->id);
        $item = $this->repo->findById($id);
        if (! $item) {
            throw new AssetItemNotFoundException($command->id);
        }
        $item->updateDetails(
            $item->getAssetCode(),
            $command->assetType,
            $command->name,
            $command->serialNumber,
            AssetCondition::from($command->condition),
            $command->notes,
        );
        $this->repo->save($item);

        return $item;
    }
}
