<?php

namespace App\Modules\Configuration\Domain\Repositories;

use App\Modules\Configuration\Infrastructure\Persistence\Eloquent\CodeGenerationRuleModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CodeGenerationRuleRepositoryInterface
{
    public function list(int $perPage = 20): LengthAwarePaginator;

    public function find(string $id): ?CodeGenerationRuleModel;

    public function findByEntityType(string $entityType): ?CodeGenerationRuleModel;

    public function save(array $attributes): CodeGenerationRuleModel;
}
