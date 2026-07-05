<?php

namespace App\Modules\Organization\Application\CommandHandlers\Position;

use App\Modules\Identity\Application\Services\AuthorizationService;
use App\Modules\Organization\Application\Commands\Position\ActivatePositionCommand;
use App\Modules\Organization\Domain\Repositories\PositionRepositoryInterface;

class ActivatePositionHandler
{
    public function __construct(
        private PositionRepositoryInterface $positionRepository,
        private AuthorizationService $authorizationService,
    ) {}

    public function handle(ActivatePositionCommand $command, string $userId): void
    {
        $this->authorizationService->requirePermission($userId, 'organization.position.update');

        $position = $this->positionRepository->findById($command->id);
        $position->activate();
        $this->positionRepository->saveAndDispatch($position);
    }
}
