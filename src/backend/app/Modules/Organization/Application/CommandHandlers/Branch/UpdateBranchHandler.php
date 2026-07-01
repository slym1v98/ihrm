<?php

namespace App\Modules\Organization\Application\CommandHandlers\Branch;

use App\Modules\Organization\Application\Commands\Branch\UpdateBranchCommand;
use App\Modules\Organization\Domain\Repositories\BranchRepositoryInterface;
use App\Modules\Identity\Application\Services\AuthorizationService;

class UpdateBranchHandler
{
    public function __construct(
        private BranchRepositoryInterface $branchRepository,
        private AuthorizationService $authorizationService,
    ) {}

    public function handle(UpdateBranchCommand $command, string $userId): void
    {
        $this->authorizationService->requirePermission($userId, 'organization.branch.update');

        $branch = $this->branchRepository->findById($command->id);
        $branch->update($command->name, $command->address, $command->phone, $command->email);
        $this->branchRepository->saveAndDispatch($branch);
    }
}
