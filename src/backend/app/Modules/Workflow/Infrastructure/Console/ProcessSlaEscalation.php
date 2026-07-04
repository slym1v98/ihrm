<?php

namespace App\Modules\Workflow\Infrastructure\Console;

use App\Modules\Workflow\Application\Services\ResolverRegistry;
use App\Modules\Workflow\Domain\Aggregates\WorkflowRequest\WorkflowRequestId;
use App\Modules\Workflow\Domain\Aggregates\WorkflowTemplate\WorkflowTemplateId;
use App\Modules\Workflow\Domain\Repositories\WorkflowRequestRepositoryInterface;
use App\Modules\Workflow\Domain\Repositories\WorkflowTemplateRepositoryInterface;
use App\Modules\Workflow\Infrastructure\Persistence\Eloquent\WorkflowRequestModel;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class ProcessSlaEscalation extends Command
{
    protected $signature = 'workflow:sla-escalate';
    protected $description = 'Check overdue SLA deadlines and escalate';

    public function handle(
        WorkflowRequestRepositoryInterface $requests,
        WorkflowTemplateRepositoryInterface $templates,
        ResolverRegistry $resolvers,
    ): int {
        $overdue = WorkflowRequestModel::where('status', 'in_review')
            ->where('escalated', false)
            ->whereNotNull('sla_deadline_at')
            ->where('sla_deadline_at', '<', CarbonImmutable::now())
            ->get();

        $count = 0;
        foreach ($overdue as $model) {
            $request = $requests->findById(new WorkflowRequestId($model->id));
            if ($request === null) continue;
            $template = $templates->findById($request->workflowTemplateId());
            if ($template === null) continue;

            $step = null;
            foreach ($template->steps() as $s) {
                if ($s->stepOrder() === $request->currentStep()) { $step = $s; break; }
            }
            if ($step === null || $step->escalationSlaHours() === null) continue;

            if ($step->escalationTargetType() !== null) {
                try {
                    $resolvers->get($step->escalationTargetType())->resolve(
                        $step->escalationTargetConfig() ?? [],
                        $request->context() ?? [],
                    );
                } catch (\Throwable) {}
            }

            $request->setEscalated(true);
            $requests->save($request);
            $count++;
        }

        $this->info("Escalated {$count} overdue requests");
        return 0;
    }
}
