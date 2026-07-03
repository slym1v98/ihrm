<?php

namespace App\Modules\Performance\Infrastructure\Persistence\Repositories;

use App\Modules\Performance\Domain\Aggregates\CompetencyTemplate\CompetencyTemplate;
use App\Modules\Performance\Domain\Aggregates\CompetencyTemplate\CompetencyTemplateId;
use App\Modules\Performance\Domain\Repositories\CompetencyTemplateRepositoryInterface;
use App\Modules\Performance\Infrastructure\Persistence\Eloquent\CompetencyTemplateModel;

class EloquentCompetencyTemplateRepository implements CompetencyTemplateRepositoryInterface
{
    public function findById(CompetencyTemplateId $id): ?CompetencyTemplate { $m = CompetencyTemplateModel::find($id->value); return $m ? $this->toDomain($m) : null; }
    public function findByCode(string $code): ?CompetencyTemplate { $m = CompetencyTemplateModel::where('code', $code)->first(); return $m ? $this->toDomain($m) : null; }
    public function all(): array { return CompetencyTemplateModel::orderBy('code')->get()->map(fn($m) => $this->toDomain($m))->toArray(); }
    public function save(CompetencyTemplate $template): void { CompetencyTemplateModel::updateOrCreate(['id' => $template->getId()->value], ['code' => $template->getCode(), 'name' => $template->getName(), 'rules' => $template->getRules(), 'active' => $template->isActive()]); }
    public function delete(CompetencyTemplateId $id): void { CompetencyTemplateModel::where('id', $id->value)->delete(); }
    private function toDomain(CompetencyTemplateModel $m): CompetencyTemplate { return CompetencyTemplate::reconstitute(CompetencyTemplateId::fromString($m->id), $m->code, $m->name, $m->rules ?? [], (bool) $m->active); }
}
