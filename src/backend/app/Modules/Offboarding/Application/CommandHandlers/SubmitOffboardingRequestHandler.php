<?php

namespace App\Modules\Offboarding\Application\CommandHandlers;

use App\Modules\Offboarding\Application\Commands\SubmitOffboardingRequestCommand;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingRequest\OffboardingRequestId;
use App\Modules\Offboarding\Domain\Repositories\OffboardingRequestRepositoryInterface;
use App\Modules\Offboarding\Domain\Exceptions\OffboardingRequestNotFoundException;

class SubmitOffboardingRequestHandler
{
    public function __construct(
        private readonly OffboardingRequestRepositoryInterface $requestRepo,
    ) {}

    public function handle(SubmitOffboardingRequestCommand $command): void
    {
        $request = $this->requestRepo->findById(OffboardingRequestId::fromString($command->requestId));
        if (!$request) { throw new OffboardingRequestNotFoundException($command->requestId); }
        $request->submit($command->workflowTemplateId ? 'wf-' . $command->requestId : null);
        $this->requestRepo->save($request);
        foreach ($request->popRecordedEvents() as $event) { event($event); }
    }
}
