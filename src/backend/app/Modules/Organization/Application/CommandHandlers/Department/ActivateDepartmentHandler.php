<?php

namespace App\Modules\Organization\Application\CommandHandlers\Department;

use App\Modules\Identity\Application\Services\AuthorizationService;
use App\Modules\Organization\Application\Commands\Department\ActivateDepartmentCommand;
use App\Modules\Organization\Domain\Repositories\DepartmentRepositoryInterface;

class ActivateDepartmentHandler
{
    public function __construct(
        private DepartmentRepositoryInterface $departmentRepository,
        private AuthorizationService $authorizationService,
    ) {}

    public function handle(ActivateDepartmentCommand $command, string $userId): void
    {
        $this->authorizationService->requirePermission($userId, 'organization.department.update');

        $department = $this->departmentRepository->findById($command->id);
        $department->activate();
        $this->departmentRepository->saveAndDispatch($department);
    }
}
