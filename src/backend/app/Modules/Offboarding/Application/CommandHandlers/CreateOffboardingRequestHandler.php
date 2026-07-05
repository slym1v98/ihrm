<?php

namespace App\Modules\Offboarding\Application\CommandHandlers;

use App\Modules\Offboarding\Application\Commands\CreateOffboardingRequestCommand;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingRequest\OffboardingRequest;
use App\Modules\Offboarding\Domain\Aggregates\OffboardingRequest\OffboardingRequestId;
use App\Modules\Offboarding\Domain\Repositories\OffboardingRequestRepositoryInterface;
use App\Modules\Offboarding\Domain\ValueObjects\OffboardingRequestType;

class CreateOffboardingRequestHandler
{
    public function __construct(
        private readonly OffboardingRequestRepositoryInterface $requestRepo,
    ) {}

    public function handle(CreateOffboardingRequestCommand $command): OffboardingRequest
    {
        $request = OffboardingRequest::create(
            OffboardingRequestId::generate(),
            $command->employeeId,
            OffboardingRequestType::from($command->type),
            $command->reason,
            new \DateTimeImmutable($command->requestedLastWorkingDate),
        );
        $this->requestRepo->save($request);

        return $request;
    }
}
