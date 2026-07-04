<?php

namespace App\Modules\Workflow\Application\Services;

use App\Modules\Workflow\Domain\Aggregates\WorkflowRequest\WorkflowRequest;
use App\Modules\Workflow\Domain\Aggregates\WorkflowTemplate\WorkflowStep;
use App\Modules\Workflow\Domain\Aggregates\WorkflowTemplate\WorkflowTemplate;
use App\Modules\Workflow\Domain\Repositories\WorkflowDelegationRepositoryInterface;
use Carbon\CarbonImmutable;

final readonly class WorkflowEngine
{
    public function __construct(
        private ConditionEvaluator $conditions,
        private ResolverRegistry $resolvers,
        private DelegationResolver $delegations,
        private WorkflowDelegationRepositoryInterface $delegationRepository,
    ) {}

    public function firstStep(WorkflowTemplate $template, array $context): array
    {
        return $this->resolveFrom($template, 1, $context);
    }

    public function advanceAfterApproval(WorkflowRequest $request, WorkflowTemplate $template): void
    {
        $nextOrder = ($request->currentStep() ?? 0) + 1;
        $next = $this->resolveFrom($template, $nextOrder, $request->context() ?? []);

        if ($next['step'] === null) {
            $request->markApproved();
            return;
        }

        $request->moveToStep($next['step']->stepOrder());
    }

    private function resolveFrom(WorkflowTemplate $template, int $fromOrder, array $context): array
    {
        foreach ($template->steps() as $step) {
            if ($step->stepOrder() < $fromOrder || ! $this->conditions->evaluate($step->condition(), $context)) {
                continue;
            }

            $approvers = $this->resolveApprovers($step, $context);
            $activeDelegations = [];
            foreach ($approvers as $approverId) {
                $activeDelegations = array_merge($activeDelegations, $this->delegationRepository->findActiveForDelegator($approverId, CarbonImmutable::now(), $step->resolverType()));
            }
            $delegated = $this->delegations->resolve($approvers, $activeDelegations, CarbonImmutable::now());

            return ['step' => $step, 'approvers' => $delegated->effectiveApproverIds, 'delegation_map' => $delegated->delegationMap];
        }

        return ['step' => null, 'approvers' => [], 'delegation_map' => []];
    }

    private function resolveApprovers(WorkflowStep $step, array $context): array
    {
        if ($step->resolverType() !== null) {
            return $this->resolvers->get($step->resolverType())->resolve($step->resolverConfig() ?? [], $context);
        }

        return $step->assigneeId() ? [$step->assigneeId()] : [];
    }
}
