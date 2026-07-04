<?php

namespace App\Modules\Configuration\Infrastructure\Http\Controllers\Actions;

use App\Modules\Configuration\Domain\Repositories\CodeGenerationRuleRepositoryInterface;
use App\Modules\Configuration\Infrastructure\Http\Controllers\CodeGenerationRuleController;
use App\Modules\Configuration\Infrastructure\Http\Requests\StoreCodeGenerationRuleRequest;

class StoreCodeGenerationRuleController
{
    public function __construct(private CodeGenerationRuleController $controller) {}

    public function __invoke(StoreCodeGenerationRuleRequest $request, CodeGenerationRuleRepositoryInterface $rules)
    {
        return $this->controller->store($request, $rules);
    }
}
