<?php

namespace App\Modules\Organization\Application\CommandHandlers\Branch;

use App\Modules\Identity\Application\Services\AuthorizationService;
use App\Modules\Organization\Application\Commands\Branch\ActivateBranchCommand;
use App\Modules\Organization\Domain\Repositories\BranchRepositoryInterface;

class ActivateBranchHandler
{
    public function __construct(
        private BranchRepositoryInterface $branchRepository,
        private AuthorizationService $authorizationService,
    ) {}

    public function handle(ActivateBranchCommand $command, string $userId): void
    {
        $this->authorizationService->requirePermission($userId, 'organization.branch.update');

        $branch = $this->branchRepository->findById($command->id);
        $branch->activate();
        $this->branchRepository->saveAndDispatch($branch);
    }
}
