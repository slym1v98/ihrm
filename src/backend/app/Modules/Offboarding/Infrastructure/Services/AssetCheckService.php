<?php

namespace App\Modules\Offboarding\Infrastructure\Services;

use App\Modules\Asset\Domain\Repositories\AssetAssignmentRepositoryInterface;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingPlan\OffboardingPlanId;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingRequest\OffboardingRequestId;
use App\Modules\Offboarding\Domain\Repositories\OffboardingPlanRepositoryInterface;
use App\Modules\Offboarding\Domain\Repositories\OffboardingRequestRepositoryInterface;

class AssetCheckService
{
    public function __construct(
        private readonly OffboardingPlanRepositoryInterface $planRepo,
        private readonly OffboardingRequestRepositoryInterface $requestRepo,
        private readonly AssetAssignmentRepositoryInterface $assignmentRepo,
    ) {}

    public function checkObligations(string $planId): AssetCheckResult
    {
        $plan = $this->planRepo->findById(OffboardingPlanId::fromString($planId));
        if (! $plan) {
            throw new \RuntimeException("Offboarding plan not found: {$planId}");
        }
        // getEmployeeId() on the plan returns the offboarding_request_id
        $request = $this->requestRepo->findById(
            OffboardingRequestId::fromString($plan->getEmployeeId())
        );
        if (! $request) {
            throw new \RuntimeException("Offboarding request not found for plan: {$planId}");
        }
        $employeeId = $request->getEmployeeId();
        $activeAssignments = $this->assignmentRepo->findActiveByEmployee($employeeId);
        if (count($activeAssignments) > 0) {
            $pending = array_map(
                fn ($a) => [
                    'assignment_id' => $a->getId()->value,
                    'asset_item_id' => $a->getAssetItemId()->value,
                ],
                $activeAssignments
            );

            return new AssetCheckResult(obligationsMet: false, pending: $pending);
        }

        return new AssetCheckResult(obligationsMet: true);
    }
}
