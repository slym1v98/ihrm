<?php

namespace App\Modules\Recruitment\Infrastructure\Jobs;

use App\Modules\Recruitment\Domain\Repositories\RecruitmentRequisitionRepositoryInterface;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RequisitionWorkflowApprovedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public string $workflowRequestId) {}

    public function handle(RecruitmentRequisitionRepositoryInterface $repo): void
    {
        $req = $repo->findByWorkflowRequestId($this->workflowRequestId);
        if (! $req) {
            return;
        } $req->approve(CarbonImmutable::now());
        $repo->save($req);
    }
}
