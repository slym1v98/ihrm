<?php
namespace App\Modules\Asset\Application\Commands;

class UpdateAssetItemCommand
{
    public function __construct(
        public readonly string $id,
        public readonly string $assetType,
        public readonly string $name,
        public readonly ?string $serialNumber = null,
        public readonly string $condition = 'good',
        public readonly ?string $notes = null,
    ) {}
}
