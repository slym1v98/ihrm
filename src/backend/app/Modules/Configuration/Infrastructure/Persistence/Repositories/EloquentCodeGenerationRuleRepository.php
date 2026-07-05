<?php

namespace App\Modules\Configuration\Infrastructure\Persistence\Repositories;

use App\Modules\Configuration\Domain\Repositories\CodeGenerationRuleRepositoryInterface;
use App\Modules\Configuration\Infrastructure\Persistence\Eloquent\CodeGenerationRuleModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentCodeGenerationRuleRepository implements CodeGenerationRuleRepositoryInterface
{
    public function list(int $perPage = 20): LengthAwarePaginator
    {
        return CodeGenerationRuleModel::orderBy('entity_type')->paginate($perPage);
    }

    public function find(string $id): ?CodeGenerationRuleModel
    {
        return CodeGenerationRuleModel::find($id);
    }

    public function findByEntityType(string $entityType): ?CodeGenerationRuleModel
    {
        return CodeGenerationRuleModel::where('entity_type', $entityType)->first();
    }

    public function save(array $attributes): CodeGenerationRuleModel
    {
        return CodeGenerationRuleModel::updateOrCreate(['id' => $attributes['id'] ?? null], $attributes);
    }
}
