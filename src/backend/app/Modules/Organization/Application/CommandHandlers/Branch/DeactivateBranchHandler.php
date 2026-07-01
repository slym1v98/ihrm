<?php

namespace App\Modules\Organization\Application\CommandHandlers\Branch;

use App\Modules\Organization\Application\Commands\Branch\DeactivateBranchCommand;
use App\Modules\Organization\Domain\Repositories\BranchRepositoryInterface;
use App\Modules\Identity\Application\Services\AuthorizationService;

class DeactivateBranchHandler
{
    public function __construct(
        private BranchRepositoryInterface $branchRepository,
        private AuthorizationService $authorizationService,
    ) {}

    public function handle(DeactivateBranchCommand $command, string $userId): void
    {
        $this->authorizationService->requirePermission($userId, 'organization.branch.update');

        $branch = $this->branchRepository->findById($command->id);
        $branch->deactivate(fn () => $this->branchRepository->hasActiveDepartments($command->id));
        $this->branchRepository->saveAndDispatch($branch);
    }
}
