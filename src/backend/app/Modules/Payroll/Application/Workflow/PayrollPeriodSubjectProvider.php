<?php

namespace App\Modules\Payroll\Application\Workflow;

use App\Modules\Payroll\Domain\Repositories\PayrollPeriodRepositoryInterface;
use App\Modules\Workflow\Application\Contracts\SubjectDataProvider;

final readonly class PayrollPeriodSubjectProvider implements SubjectDataProvider
{
    public function __construct(
        private PayrollPeriodRepositoryInterface $periods,
    ) {}

    public function subjectType(): string
    {
        return 'payroll_period';
    }

    public function fetchContext(string $subjectId): array
    {
        return ['subject_id' => $subjectId];
    }
}
