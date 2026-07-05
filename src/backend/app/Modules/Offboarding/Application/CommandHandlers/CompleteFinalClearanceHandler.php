<?php

namespace App\Modules\Offboarding\Application\CommandHandlers;

use App\Modules\Offboarding\Application\Commands\CompleteFinalClearanceCommand;
use App\Modules\Offboarding\Domain\Aggregates\FinalClearance\FinalClearance;
use App\Modules\Offboarding\Domain\Aggregates\FinalClearance\FinalClearanceId;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingPlan\OffboardingPlanId;
use App\Modules\Offboarding\Domain\Exceptions\AssetObligationsNotMetException;
use App\Modules\Offboarding\Domain\Exceptions\OffboardingPlanNotFoundException;
use App\Modules\Offboarding\Domain\Repositories\FinalClearanceRepositoryInterface;
use App\Modules\Offboarding\Domain\Repositories\OffboardingPlanRepositoryInterface;
use App\Modules\Offboarding\Infrastructure\Services\AssetCheckService;

class CompleteFinalClearanceHandler
{
    public function __construct(
        private readonly OffboardingPlanRepositoryInterface $planRepo,
        private readonly FinalClearanceRepositoryInterface $clearanceRepo,
        private readonly AssetCheckService $assetCheckService,
    ) {}

    public function handle(CompleteFinalClearanceCommand $command): void
    {
        $plan = $this->planRepo->findById(OffboardingPlanId::fromString($command->planId));
        if (! $plan) {
            throw new OffboardingPlanNotFoundException($command->planId);
        }

        $assetCheck = $this->assetCheckService->checkObligations($command->planId);
        if (! $assetCheck->obligationsMet) {
            throw new AssetObligationsNotMetException($assetCheck->pending);
        }

        $clearance = FinalClearance::create(
            FinalClearanceId::generate(),
            $command->planId,
            $plan->getRequestId(),
            $command->clearedBy,
            $assetCheck->obligationsMet,
            $command->payrollNotes,
        );
        $this->clearanceRepo->save($clearance);
        $plan->markWorkflowApproved();
        $this->planRepo->save($plan);
        foreach ($clearance->popRecordedEvents() as $event) {
            event($event);
        }
    }
}
