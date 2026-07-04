<?php

namespace App\Modules\Recruitment\Application\Listeners;

use App\Modules\Configuration\Application\Services\CodeGenerator;
use App\Modules\Employee\Domain\Aggregates\Employee\Employee;
use App\Modules\Employee\Domain\Aggregates\Employee\EmployeeCode;
use App\Modules\Employee\Domain\Aggregates\Employee\EmployeeId;
use App\Modules\Employee\Domain\Aggregates\Employee\PersonalName;
use App\Modules\Employee\Domain\Repositories\EmployeeRepositoryInterface;
use App\Modules\Onboarding\Application\Commands\CreateOnboardingPlanCommand;
use App\Modules\Onboarding\Application\CommandHandlers\CreateOnboardingPlanHandler;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTemplateRepositoryInterface;
use App\Modules\Recruitment\Domain\Repositories\CandidateRepositoryInterface;
use App\Modules\Recruitment\Domain\Events\CandidateHired;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Support\Facades\Event;

class CreateEmployeeOnOfferAccepted
{
    public function __construct(
        private EmployeeRepositoryInterface $employees,
        private CandidateRepositoryInterface $candidates,
        private CodeGenerator $codeGen,
        private OnboardingTemplateRepositoryInterface $templates,
        private CreateOnboardingPlanHandler $onboardingPlan,
    ) {}

    public function handle(object $event): void
    {
        $payload = $event->payload ?? [];
        $candidateId = $payload['candidate_id'] ?? null;
        if ($candidateId === null) return;

        $candidate = $this->candidates->findById(new \App\Modules\Recruitment\Domain\Aggregates\Candidate\CandidateId($candidateId));
        if ($candidate === null || $candidate->getEmployeeId() !== null) return;

        $fullName = $candidate->getFullName();
        $parts = explode(' ', $fullName);
        $lastName = array_shift($parts) ?: $fullName;
        $firstName = implode(' ', $parts) ?: '_';

        try {
            $employeeCode = EmployeeCode::fromString($this->codeGen->next('employee'));
        } catch (Exception) {
            $employeeCode = EmployeeCode::fromString('NV' . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT));
        }

        $employee = Employee::create(
            EmployeeId::generate(),
            $employeeCode,
            PersonalName::of($firstName, $lastName),
        );

        $this->employees->saveAndDispatch($employee);
        $employeeId = (string) $employee->id()->value;

        $candidate->linkEmployee($employeeId);
        $this->candidates->save($candidate);

        // Create OnboardingPlan
        try {
            $templates = $this->templates->findMatching(null, null, null, null);
            $templateId = !empty($templates) ? $templates[0]->getId()->value : null;
            $startDate = $event instanceof \App\Modules\Recruitment\Domain\Events\OfferAccepted
                ? CarbonImmutable::parse($payload['accepted_at'] ?? 'now')->toDateString()
                : date('Y-m-d');
            $this->onboardingPlan->handle(new CreateOnboardingPlanCommand(
                employeeId: $employeeId,
                candidateId: $candidateId,
                templateId: $templateId,
                startDate: $startDate,
            ));
        } catch (\Throwable) {
            // Onboarding plan is best-effort
        }

        // Dispatch CandidateHired with employee_id for other listeners
        Event::dispatch(new CandidateHired([
            'candidate_id' => $candidateId,
            'employee_id' => $employeeId,
            'full_name' => $fullName,
            'email' => $candidate->getEmail(),
            'department_id' => null,
            'position_id' => null,
            'start_date' => date('Y-m-d'),
        ]));
    }
}
