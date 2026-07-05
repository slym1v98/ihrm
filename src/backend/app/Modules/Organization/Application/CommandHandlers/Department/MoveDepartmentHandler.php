<?php

namespace App\Modules\Organization\Application\CommandHandlers\Department;

use App\Modules\Identity\Application\Services\AuthorizationService;
use App\Modules\Organization\Application\Commands\Department\MoveDepartmentCommand;
use App\Modules\Organization\Domain\Aggregates\Department\DepartmentId;
use App\Modules\Organization\Domain\Repositories\DepartmentRepositoryInterface;

class MoveDepartmentHandler
{
    public function __construct(
        private DepartmentRepositoryInterface $departmentRepository,
        private AuthorizationService $authorizationService,
    ) {}

    public function handle(MoveDepartmentCommand $command, string $userId): void
    {
        $this->authorizationService->requirePermission($userId, 'organization.department.move');

        $department = $this->departmentRepository->findById($command->id);

        $department->moveTo(
            $command->newParentId,
            fn (?DepartmentId $id) => $id !== null
                && in_array($id->value, $this->departmentRepository->findDescendantIds($department->id()), true),
            fn (DepartmentId $id) => $this->departmentRepository->findBranchIdOf($id),
        );

        $this->departmentRepository->saveAndDispatch($department);
    }
}
