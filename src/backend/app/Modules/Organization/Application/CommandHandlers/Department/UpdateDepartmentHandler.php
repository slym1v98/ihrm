<?php

namespace App\Modules\Organization\Application\CommandHandlers\Department;

use App\Modules\Identity\Application\Services\AuthorizationService;
use App\Modules\Organization\Application\Commands\Department\UpdateDepartmentCommand;
use App\Modules\Organization\Domain\Repositories\DepartmentRepositoryInterface;

class UpdateDepartmentHandler
{
    public function __construct(
        private DepartmentRepositoryInterface $departmentRepository,
        private AuthorizationService $authorizationService,
    ) {}

    public function handle(UpdateDepartmentCommand $command, string $userId): void
    {
        $this->authorizationService->requirePermission($userId, 'organization.department.update');

        $department = $this->departmentRepository->findById($command->id);
        $department->update($command->name, $command->managerEmployeeId);
        $this->departmentRepository->saveAndDispatch($department);
    }
}
