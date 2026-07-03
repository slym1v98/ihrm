<?php
namespace App\Modules\Asset\Application\Commands;

class AssignAssetCommand
{
    public function __construct(
        public readonly string $assetItemId,
        public readonly string $employeeId,
        public readonly ?string $expectedReturnAt = null,
        public readonly ?string $conditionOnIssue = null,
    ) {}
}
