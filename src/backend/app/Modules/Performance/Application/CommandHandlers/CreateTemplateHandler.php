<?php

namespace App\Modules\Performance\Application\CommandHandlers;

use App\Modules\Performance\Application\Commands\CreateTemplateCommand;
use App\Modules\Performance\Domain\Aggregates\CompetencyTemplate\CompetencyTemplate;
use App\Modules\Performance\Domain\Aggregates\CompetencyTemplate\CompetencyTemplateId;
use App\Modules\Performance\Domain\Repositories\CompetencyTemplateRepositoryInterface;

class CreateTemplateHandler
{
    public function __construct(private readonly CompetencyTemplateRepositoryInterface $repo) {}

    public function handle(CreateTemplateCommand $cmd): CompetencyTemplate
    {
        $t = CompetencyTemplate::create(CompetencyTemplateId::generate(), $cmd->code, $cmd->name, $cmd->rules);
        $this->repo->save($t);

        return $t;
    }
}
