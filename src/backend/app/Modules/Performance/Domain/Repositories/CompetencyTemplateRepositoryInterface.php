<?php

namespace App\Modules\Performance\Domain\Repositories;

use App\Modules\Performance\Domain\Aggregates\CompetencyTemplate\CompetencyTemplate;
use App\Modules\Performance\Domain\Aggregates\CompetencyTemplate\CompetencyTemplateId;

interface CompetencyTemplateRepositoryInterface
{
    public function findById(CompetencyTemplateId $id): ?CompetencyTemplate;

    public function findByCode(string $code): ?CompetencyTemplate;

    public function all(): array;

    public function save(CompetencyTemplate $template): void;

    public function delete(CompetencyTemplateId $id): void;
}
