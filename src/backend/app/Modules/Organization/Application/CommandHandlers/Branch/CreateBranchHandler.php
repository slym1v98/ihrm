<?php

namespace App\Modules\Organization\Application\CommandHandlers\Branch;

use App\Modules\Identity\Application\Services\AuthorizationService;
use App\Modules\Organization\Application\Commands\Branch\CreateBranchCommand;
use App\Modules\Organization\Domain\Aggregates\Branch\Branch;
use App\Modules\Organization\Domain\Aggregates\Branch\BranchId;
use App\Modules\Organization\Domain\Exceptions\DuplicateBranchCodeException;
use App\Modules\Organization\Domain\Repositories\BranchRepositoryInterface;
use Ramsey\Uuid\Uuid;

class CreateBranchHandler
{
    public function __construct(
        private BranchRepositoryInterface $branchRepository,
        private AuthorizationService $authorizationService,
    ) {}

    public function handle(CreateBranchCommand $command, string $userId): Branch
    {
        $this->authorizationService->requirePermission($userId, 'organization.branch.create');

        if ($this->branchRepository->existsByCode($command->code)) {
            throw new DuplicateBranchCodeException($command->code->value);
        }

        $branch = Branch::create(
            BranchId::fromString(Uuid::uuid4()->toString()),
            $command->code,
            $command->name,
            $command->address,
            $command->phone,
            $command->email,
        );

        $this->branchRepository->saveAndDispatch($branch);

        return $branch;
    }
}
