<?php

namespace App\Modules\Organization\Application\QueryHandlers\Branch;

use App\Modules\Organization\Application\Queries\Branch\GetBranchQuery;
use App\Modules\Organization\Domain\Aggregates\Branch\Branch;
use App\Modules\Organization\Domain\Repositories\BranchRepositoryInterface;

class GetBranchHandler
{
    public function __construct(private BranchRepositoryInterface $branchRepository) {}

    public function handle(GetBranchQuery $query): Branch
    {
        return $this->branchRepository->findById($query->id);
    }
}
