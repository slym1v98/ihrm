<?php

namespace App\Modules\Performance\Application\CommandHandlers;

use App\Modules\Performance\Application\Commands\UpdateTemplateCommand;
use App\Modules\Performance\Domain\Aggregates\CompetencyTemplate\CompetencyTemplateId;
use App\Modules\Performance\Domain\Repositories\CompetencyTemplateRepositoryInterface;
use App\Modules\Performance\Domain\Exceptions\CompetencyTemplateNotFoundException;

class UpdateTemplateHandler
{
    public function __construct(private readonly CompetencyTemplateRepositoryInterface $repo) {}
    public function handle(UpdateTemplateCommand $cmd): void
    {
        $t = $this->repo->findById(CompetencyTemplateId::fromString($cmd->id)) ?? throw new CompetencyTemplateNotFoundException($cmd->id);
        $t->update($cmd->code, $cmd->name, $cmd->rules);
        $this->repo->save($t);
    }
}
