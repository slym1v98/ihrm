<?php

namespace App\Modules\Organization\Application\CommandHandlers\Position;

use App\Modules\Identity\Application\Services\AuthorizationService;
use App\Modules\Organization\Application\Commands\Position\DeactivatePositionCommand;
use App\Modules\Organization\Domain\Repositories\PositionRepositoryInterface;

class DeactivatePositionHandler
{
    public function __construct(
        private PositionRepositoryInterface $positionRepository,
        private AuthorizationService $authorizationService,
    ) {}

    public function handle(DeactivatePositionCommand $command, string $userId): void
    {
        $this->authorizationService->requirePermission($userId, 'organization.position.update');

        $position = $this->positionRepository->findById($command->id);
        $position->deactivate();
        $this->positionRepository->saveAndDispatch($position);
    }
}
