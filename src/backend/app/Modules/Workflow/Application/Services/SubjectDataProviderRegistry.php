<?php
namespace App\Modules\Workflow\Application\Services;
use App\Modules\Workflow\Application\Contracts\SubjectDataProvider;
use App\Modules\Workflow\Domain\Exceptions\WorkflowSubjectProviderNotFoundException;
final class SubjectDataProviderRegistry
{
    private array $providers = [];
    public function register(SubjectDataProvider $p): void { $this->providers[$p->subjectType()] = $p; }
    public function get(string $t): SubjectDataProvider {
        if (!isset($this->providers[$t])) throw new WorkflowSubjectProviderNotFoundException("Không tìm thấy subject provider cho loại {$t}");
        return $this->providers[$t];
    }
}
