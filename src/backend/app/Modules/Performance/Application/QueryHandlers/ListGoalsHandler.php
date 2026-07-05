<?php

namespace App\Modules\Performance\Application\QueryHandlers;

use App\Modules\Performance\Application\Queries\ListGoalsQuery;
use App\Modules\Performance\Domain\Repositories\GoalRepositoryInterface;

class ListGoalsHandler
{
    public function __construct(private readonly GoalRepositoryInterface $repo) {}

    public function handle(ListGoalsQuery $q): array
    {
        $items = $this->repo->findByCycleId($q->cycleId);
        if ($q->employeeId) {
            $items = array_values(array_filter($items, fn ($g) => $g->getEmployeeId() === $q->employeeId));
        }

        return $items;
    }
}
