<?php

namespace App\Modules\Configuration\Infrastructure\Http\Controllers\Actions;

use App\Modules\Configuration\Application\Services\CodeGenerator;
use App\Modules\Configuration\Infrastructure\Http\Controllers\CodeGenerationRuleController;

class PreviewCodeGenerationRuleController
{
    public function __construct(private CodeGenerationRuleController $controller) {}

    public function __invoke(string $entityType, CodeGenerator $generator)
    {
        return $this->controller->preview($entityType, $generator);
    }
}
