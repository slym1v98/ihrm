<?php

namespace App\Modules\Asset\Application\CommandHandlers;

use App\Modules\Asset\Application\Commands\CreateAssetItemCommand;
use App\Modules\Asset\Domain\Aggregates\AssetItem\AssetItem;
use App\Modules\Asset\Domain\Repositories\AssetItemRepositoryInterface;
use App\Modules\Asset\Domain\ValueObjects\AssetCondition;
use App\Modules\Asset\Domain\ValueObjects\AssetItemId;

class CreateAssetItemHandler
{
    public function __construct(
        private readonly AssetItemRepositoryInterface $repo,
    ) {}

    public function handle(CreateAssetItemCommand $command): AssetItem
    {
        $item = AssetItem::create(
            AssetItemId::generate(),
            $command->assetCode,
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
