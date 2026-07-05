<?php

namespace App\Modules\Performance\Application\QueryHandlers;

use App\Modules\Performance\Application\Queries\ListCyclesQuery;
use App\Modules\Performance\Domain\Repositories\PerformanceCycleRepositoryInterface;

class ListCyclesHandler
{
    public function __construct(private readonly PerformanceCycleRepositoryInterface $repo) {}

    public function handle(ListCyclesQuery $q): array
    {
        $items = $this->repo->all();
        if ($q->status) {
            $items = array_values(array_filter($items, fn ($c) => $c->getStatus()->value === $q->status));
        }

        return $items;
    }
}
