<?php

namespace App\Modules\Organization\Application\CommandHandlers\Position;

use App\Modules\Organization\Application\Commands\Position\CreatePositionCommand;
use App\Modules\Organization\Domain\Aggregates\Position\Position;
use App\Modules\Organization\Domain\Aggregates\Position\PositionId;
use App\Modules\Organization\Domain\Exceptions\DuplicatePositionCodeException;
use App\Modules\Organization\Domain\Repositories\PositionRepositoryInterface;
use App\Modules\Identity\Application\Services\AuthorizationService;
use Ramsey\Uuid\Uuid;

class CreatePositionHandler
{
    public function __construct(
        private PositionRepositoryInterface $positionRepository,
        private AuthorizationService $authorizationService,
    ) {}

    public function handle(CreatePositionCommand $command, string $userId): Position
    {
        $this->authorizationService->requirePermission($userId, 'organization.position.create');

        if ($this->positionRepository->existsByCode($command->code)) {
            throw new DuplicatePositionCodeException($command->code->value);
        }

        $position = Position::create(
            PositionId::fromString(Uuid::uuid4()->toString()),
            $command->code,
            $command->name,
            $command->level,
            $command->description,
        );

        $this->positionRepository->saveAndDispatch($position);

        return $position;
    }
}
