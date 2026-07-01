<?php

namespace App\Modules\Organization\Application\CommandHandlers\Department;

use App\Modules\Organization\Application\Commands\Department\DeactivateDepartmentCommand;
use App\Modules\Organization\Domain\Repositories\DepartmentRepositoryInterface;
use App\Modules\Identity\Application\Services\AuthorizationService;

class DeactivateDepartmentHandler
{
    public function __construct(
        private DepartmentRepositoryInterface $departmentRepository,
        private AuthorizationService $authorizationService,
    ) {}

    public function handle(DeactivateDepartmentCommand $command, string $userId): void
    {
        $this->authorizationService->requirePermission($userId, 'organization.department.update');

        $department = $this->departmentRepository->findById($command->id);
        $department->deactivate(fn () => $this->departmentRepository->hasActiveChildren($command->id));
        $this->departmentRepository->saveAndDispatch($department);
    }
}
