<?php

namespace App\Modules\Offboarding\Infrastructure\Services;

class AssetCheckResult
{
    public function __construct(
        public readonly bool $obligationsMet,
        public readonly array $pending = [],
    ) {}
}

class AssetCheckService
{
    public function checkObligations(string $planId): AssetCheckResult
    {
        // Stub: Asset BC not yet built. Returns obligationsMet=true.
        return new AssetCheckResult(obligationsMet: true);
    }
}
