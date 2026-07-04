<?php

namespace App\Modules\Configuration\Infrastructure\Http\Controllers\Actions;

use App\Modules\Configuration\Domain\Repositories\CodeGenerationRuleRepositoryInterface;
use App\Modules\Configuration\Infrastructure\Http\Controllers\CodeGenerationRuleController;
use Illuminate\Http\Request;

class ListCodeGenerationRuleController
{
    public function __construct(private CodeGenerationRuleController $controller) {}

    public function __invoke(Request $request, CodeGenerationRuleRepositoryInterface $rules)
    {
        return $this->controller->index($request, $rules);
    }
}
