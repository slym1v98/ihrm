<?php

namespace App\Modules\Organization\Application\CommandHandlers\Position;

use App\Modules\Identity\Application\Services\AuthorizationService;
use App\Modules\Organization\Application\Commands\Position\UpdatePositionCommand;
use App\Modules\Organization\Domain\Repositories\PositionRepositoryInterface;

class UpdatePositionHandler
{
    public function __construct(
        private PositionRepositoryInterface $positionRepository,
        private AuthorizationService $authorizationService,
    ) {}

    public function handle(UpdatePositionCommand $command, string $userId): void
    {
        $this->authorizationService->requirePermission($userId, 'organization.position.update');

        $position = $this->positionRepository->findById($command->id);
        $position->update($command->name, $command->level, $command->description);
        $this->positionRepository->saveAndDispatch($position);
    }
}
