<?php
namespace App\Modules\Asset\Application\Commands;

class ReturnAssetCommand
{
    public function __construct(
        public readonly string $assignmentId,
        public readonly string $conditionOnReturn,
        public readonly ?string $notes = null,
        public readonly float $settlementAmount = 0.0,
    ) {}
}
