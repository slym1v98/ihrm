<?php

namespace App\Modules\Offboarding\Application\CommandHandlers;

use App\Modules\Offboarding\Application\Commands\ApproveOffboardingRequestCommand;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingRequest\OffboardingRequestId;
use App\Modules\Offboarding\Domain\Repositories\OffboardingRequestRepositoryInterface;
use App\Modules\Offboarding\Domain\Exceptions\OffboardingRequestNotFoundException;

class ApproveOffboardingRequestHandler
{
    public function __construct(
        private readonly OffboardingRequestRepositoryInterface $requestRepo,
    ) {}

    public function handle(ApproveOffboardingRequestCommand $command): void
    {
        $request = $this->requestRepo->findById(OffboardingRequestId::fromString($command->requestId));
        if (!$request) { throw new OffboardingRequestNotFoundException($command->requestId); }
        $request->approve(new \DateTimeImmutable($command->approvedLastWorkingDate));
        $this->requestRepo->save($request);
        foreach ($request->popRecordedEvents() as $event) { event($event); }
    }
}
