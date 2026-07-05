<?php

namespace App\Modules\Organization\Application\CommandHandlers\Department;

use App\Modules\Identity\Application\Services\AuthorizationService;
use App\Modules\Organization\Application\Commands\Department\CreateDepartmentCommand;
use App\Modules\Organization\Domain\Aggregates\Department\Department;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentId;
use App\Modules\Organization\Domain\Exceptions\DuplicateDepartmentCodeException;
use App\Modules\Organization\Domain\Repositories\BranchRepositoryInterface;
use App\Modules\Organization\Domain\Repositories\DepartmentRepositoryInterface;
use Ramsey\Uuid\Uuid;

class CreateDepartmentHandler
{
    public function __construct(
        private DepartmentRepositoryInterface $departmentRepository,
        private BranchRepositoryInterface $branchRepository,
        private AuthorizationService $authorizationService,
    ) {}

    public function handle(CreateDepartmentCommand $command, string $userId): Department
    {
        $this->authorizationService->requirePermission($userId, 'organization.department.create');

        // Validate branch exists
        $this->branchRepository->findById($command->branchId);

        if ($this->departmentRepository->existsByCodeAndBranch($command->code, $command->branchId)) {
            throw new DuplicateDepartmentCodeException($command->code->value);
        }

        $department = Department::create(
            DepartmentId::fromString(Uuid::uuid4()->toString()),
            $command->code,
            $command->name,
            $command->branchId,
            $command->parentId,
        );

        $this->departmentRepository->saveAndDispatch($department);

        return $department;
    }
}
