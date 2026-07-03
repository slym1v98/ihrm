<?php
namespace App\Modules\Asset\Application\Commands;

class CreateAssetItemCommand
{
    public function __construct(
        public readonly string $assetCode,
        public readonly string $assetType,
        public readonly string $name,
        public readonly ?string $serialNumber = null,
        public readonly string $condition = 'new',
        public readonly ?string $notes = null,
    ) {}
}
