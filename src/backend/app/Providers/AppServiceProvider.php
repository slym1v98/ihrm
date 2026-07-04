<?php

namespace App\Providers;

use App\Modules\Audit\Infrastructure\Listeners\AuditEventListener;
use App\Modules\Configuration\Domain\Repositories\CodeGenerationRuleRepositoryInterface;
use App\Modules\Configuration\Domain\Repositories\HolidayCalendarRepositoryInterface;
use App\Modules\Configuration\Domain\Repositories\LookupRepositoryInterface;
use App\Modules\Configuration\Domain\Repositories\NotificationThresholdRepositoryInterface;
use App\Modules\Configuration\Domain\Repositories\SystemSettingRepositoryInterface;
use App\Modules\Configuration\Infrastructure\Persistence\Repositories\EloquentCodeGenerationRuleRepository;
use App\Modules\Configuration\Infrastructure\Persistence\Repositories\EloquentHolidayCalendarRepository;
use App\Modules\Configuration\Infrastructure\Persistence\Repositories\EloquentLookupRepository;
use App\Modules\Configuration\Infrastructure\Persistence\Repositories\EloquentNotificationThresholdRepository;
use App\Modules\Configuration\Infrastructure\Persistence\Repositories\EloquentSystemSettingRepository;
use App\Modules\Employee\Domain\Repositories\ContractRepositoryInterface;
use App\Modules\Employee\Domain\Repositories\EmployeeDocumentRepositoryInterface;
use App\Modules\Employee\Domain\Repositories\EmployeeRepositoryInterface;
use App\Modules\Employee\Infrastructure\Persistence\Repositories\EloquentContractRepository;
use App\Modules\Employee\Infrastructure\Persistence\Repositories\EloquentEmployeeDocumentRepository;
use App\Modules\Employee\Infrastructure\Persistence\Repositories\EloquentEmployeeRepository;
use App\Modules\Shift\Domain\Repositories\ShiftAssignmentRepositoryInterface;
use App\Modules\Shift\Domain\Repositories\ShiftTemplateRepositoryInterface;
use App\Modules\Shift\Infrastructure\Persistence\Repositories\EloquentShiftAssignmentRepository;
use App\Modules\Shift\Infrastructure\Persistence\Repositories\EloquentShiftTemplateRepository;
use App\Modules\Leave\Domain\Repositories\LeaveBalanceRepositoryInterface;
use App\Modules\Leave\Domain\Repositories\LeavePolicyRepositoryInterface;
use App\Modules\Leave\Domain\Repositories\LeaveRequestRepositoryInterface;
use App\Modules\Leave\Domain\Repositories\LeaveTypeRepositoryInterface;
use App\Modules\Leave\Domain\Services\LeaveWindowInterface;
use App\Modules\Leave\Infrastructure\Persistence\Repositories\EloquentLeaveBalanceRepository;
use App\Modules\Leave\Infrastructure\Persistence\Repositories\EloquentLeavePolicyRepository;
use App\Modules\Leave\Infrastructure\Persistence\Repositories\EloquentLeaveRequestRepository;
use App\Modules\Leave\Infrastructure\Persistence\Repositories\EloquentLeaveTypeRepository;
use App\Modules\Leave\Infrastructure\Persistence\Repositories\EloquentLeaveWindowRepository;
use App\Modules\Leave\Application\Workflow\Listeners\SyncLeaveRequestOnWorkflowApproved;
use App\Modules\Leave\Application\Workflow\Listeners\SyncLeaveRequestOnWorkflowRejected;
use App\Modules\Workflow\Domain\Events\WorkflowApproved;
use App\Modules\Workflow\Domain\Events\WorkflowRejected;
use App\Modules\Recruitment\Domain\Events\OfferAccepted;
use App\Modules\Recruitment\Application\Listeners\CreateEmployeeOnOfferAccepted;
use App\Modules\Employee\Application\Listeners\ActivateEmployeeOnOnboardingComplete;
use App\Modules\Employee\Application\Listeners\CreateOffboardingOnResign;
use App\Modules\Onboarding\Domain\Events\OnboardingPlanCompleted;
use App\Modules\Leave\Application\Workflow\LeaveRequestSubjectProvider;
use App\Modules\Workflow\Application\Contracts\SubjectDataProvider;
use App\Modules\Workflow\Application\Resolvers\DepartmentHeadResolver;
use App\Modules\Workflow\Application\Resolvers\DirectManagerResolver;
use App\Modules\Workflow\Application\Resolvers\RoleInDepartmentResolver;
use App\Modules\Workflow\Application\Resolvers\RoleResolver;
use App\Modules\Workflow\Application\Resolvers\SpecificUserResolver;
use App\Modules\Attendance\Application\Workflow\AttendancePeriodSubjectProvider;
use App\Modules\Payroll\Application\Workflow\PayrollPeriodSubjectProvider;
use App\Modules\Workflow\Application\Services\ConditionEvaluator;
use App\Modules\Workflow\Application\Services\DelegationResolver;
use App\Modules\Workflow\Application\Services\ResolverRegistry;
use App\Modules\Workflow\Application\Services\SubjectDataProviderRegistry;
use App\Modules\Workflow\Application\Services\WorkflowEngine;
use App\Modules\Workflow\Domain\Repositories\WorkflowTemplateRepositoryInterface;
use App\Modules\Workflow\Domain\Repositories\WorkflowRequestRepositoryInterface;
use App\Modules\Workflow\Domain\Repositories\WorkflowDelegationRepositoryInterface;
use App\Modules\Workflow\Infrastructure\Persistence\Repositories\EloquentWorkflowTemplateRepository;
use App\Modules\Workflow\Infrastructure\Persistence\Repositories\EloquentWorkflowRequestRepository;
use App\Modules\Workflow\Infrastructure\Persistence\Repositories\EloquentWorkflowDelegationRepository;
use App\Modules\Attendance\Domain\Repositories\AttendanceRawLogRepositoryInterface;
use App\Modules\Attendance\Domain\Repositories\AttendanceTimesheetRepositoryInterface;
use App\Modules\Attendance\Domain\Repositories\AttendanceAdjustmentRequestRepositoryInterface;
use App\Modules\Attendance\Domain\Repositories\AttendancePeriodRepositoryInterface;
use App\Modules\Attendance\Infrastructure\Persistence\Repositories\EloquentAttendanceRawLogRepository;
use App\Modules\Attendance\Infrastructure\Persistence\Repositories\EloquentAttendanceTimesheetRepository;
use App\Modules\Attendance\Infrastructure\Persistence\Repositories\EloquentAttendanceAdjustmentRequestRepository;
use App\Modules\Attendance\Infrastructure\Persistence\Repositories\EloquentAttendancePeriodRepository;
use App\Modules\Payroll\Domain\Repositories\PayrollPeriodRepositoryInterface;
use App\Modules\Payroll\Domain\Repositories\PayrollComponentRepositoryInterface;
use App\Modules\Payroll\Domain\Repositories\PayrollRunRepositoryInterface;
use App\Modules\Payroll\Domain\Repositories\PayrollEntryRepositoryInterface;
use App\Modules\Payroll\Domain\Repositories\PayrollAdjustmentRepositoryInterface;
use App\Modules\Payroll\Domain\Repositories\PayslipRepositoryInterface;
use App\Modules\Payroll\Domain\Ports\AttendanceReadPort;
use App\Modules\Payroll\Domain\Ports\LeaveReadPort;
use App\Modules\Payroll\Domain\Ports\EmployeeContractReadPort;
use App\Modules\Payroll\Infrastructure\Persistence\Repositories\EloquentPayrollPeriodRepository;
use App\Modules\Payroll\Infrastructure\Persistence\Repositories\EloquentPayrollComponentRepository;
use App\Modules\Payroll\Infrastructure\Persistence\Repositories\EloquentPayrollRunRepository;
use App\Modules\Payroll\Infrastructure\Persistence\Repositories\EloquentPayrollEntryRepository;
use App\Modules\Payroll\Infrastructure\Persistence\Repositories\EloquentPayrollAdjustmentRepository;
use App\Modules\Payroll\Infrastructure\Persistence\Repositories\EloquentPayslipRepository;
use App\Modules\Payroll\Infrastructure\Ports\DatabaseAttendanceReadPort;
use App\Modules\Payroll\Infrastructure\Ports\DatabaseLeaveReadPort;
use App\Modules\Payroll\Infrastructure\Ports\DatabaseEmployeeContractReadPort;

use App\Modules\Employee\Domain\Events\ContractActivated;
use App\Modules\Employee\Domain\Events\ContractCreated;
use App\Modules\Employee\Domain\Events\ContractExpired;
use App\Modules\Employee\Domain\Events\ContractRenewed;
use App\Modules\Employee\Domain\Events\ContractTerminated;
use App\Modules\Employee\Domain\Events\EmployeeCreated;
use App\Modules\Employee\Domain\Events\EmployeeDocumentArchived;
use App\Modules\Employee\Domain\Events\EmployeeDocumentExpired;
use App\Modules\Employee\Domain\Events\EmployeeDocumentReplaced;
use App\Modules\Employee\Domain\Events\EmployeeDocumentUploaded;
use App\Modules\Employee\Domain\Events\EmployeeEmploymentChanged;
use App\Modules\Employee\Domain\Events\EmployeeManagerChanged;
use App\Modules\Employee\Domain\Events\EmployeePersonalInfoUpdated;
use App\Modules\Employee\Domain\Events\EmployeeStatusChanged;
use App\Modules\Identity\Domain\Repositories\RoleRepositoryInterface;
use App\Modules\Identity\Domain\Repositories\UserRepositoryInterface;
use App\Modules\Organization\Domain\Repositories\BranchRepositoryInterface;
use App\Modules\Organization\Domain\Repositories\DepartmentRepositoryInterface;
use App\Modules\Organization\Domain\Repositories\PositionRepositoryInterface;
use App\Modules\Organization\Domain\Events\BranchActivated;
use App\Modules\Organization\Domain\Events\BranchCreated;
use App\Modules\Organization\Domain\Events\BranchDeactivated;
use App\Modules\Organization\Domain\Events\BranchUpdated;
use App\Modules\Organization\Domain\Events\DepartmentActivated;
use App\Modules\Organization\Domain\Events\DepartmentCreated;
use App\Modules\Organization\Domain\Events\DepartmentDeactivated;
use App\Modules\Organization\Domain\Events\DepartmentMoved;
use App\Modules\Organization\Domain\Events\DepartmentUpdated;
use App\Modules\Organization\Domain\Events\PositionActivated;
use App\Modules\Organization\Domain\Events\PositionCreated;
use App\Modules\Organization\Domain\Events\PositionDeactivated;
use App\Modules\Organization\Domain\Events\PositionUpdated;
use App\Modules\Organization\Infrastructure\Persistence\Repositories\EloquentBranchRepository;
use App\Modules\Organization\Infrastructure\Persistence\Repositories\EloquentDepartmentRepository;
use App\Modules\Organization\Infrastructure\Persistence\Repositories\EloquentPositionRepository;
use App\Modules\Identity\Infrastructure\Persistence\Repositories\EloquentRoleRepository;
use App\Modules\Identity\Infrastructure\Persistence\Repositories\EloquentUserRepository;
use App\Modules\Identity\Domain\Events\RoleCreated;
use App\Modules\Identity\Domain\Events\RolePermissionGranted;
use App\Modules\Identity\Domain\Events\RolePermissionRevoked;
use App\Modules\Identity\Domain\Events\RoleUpdated;
use App\Modules\Identity\Domain\Events\UserCreated;
use App\Modules\Identity\Domain\Events\UserDataScopeGranted;
use App\Modules\Identity\Domain\Events\UserDisabled;
use App\Modules\Identity\Domain\Events\UserLoggedIn;
use App\Modules\Identity\Domain\Events\UserLoginFailed;
use App\Modules\Identity\Domain\Events\UserPasswordChanged;
use App\Modules\Identity\Domain\Events\UserReactivated;
use App\Modules\Identity\Domain\Events\UserRoleAssigned;
use App\Modules\Identity\Domain\Events\UserRoleRevoked;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, EloquentRoleRepository::class);
        $this->app->bind(LookupRepositoryInterface::class, EloquentLookupRepository::class);
        $this->app->bind(CodeGenerationRuleRepositoryInterface::class, EloquentCodeGenerationRuleRepository::class);
        $this->app->bind(SystemSettingRepositoryInterface::class, EloquentSystemSettingRepository::class);
        $this->app->bind(HolidayCalendarRepositoryInterface::class, EloquentHolidayCalendarRepository::class);
        $this->app->bind(NotificationThresholdRepositoryInterface::class, EloquentNotificationThresholdRepository::class);
        $this->app->bind(BranchRepositoryInterface::class, EloquentBranchRepository::class);
        $this->app->bind(DepartmentRepositoryInterface::class, EloquentDepartmentRepository::class);
        $this->app->bind(PositionRepositoryInterface::class, EloquentPositionRepository::class);
        $this->app->bind(EmployeeRepositoryInterface::class, EloquentEmployeeRepository::class);
        $this->app->bind(ContractRepositoryInterface::class, EloquentContractRepository::class);
        $this->app->bind(EmployeeDocumentRepositoryInterface::class, EloquentEmployeeDocumentRepository::class);
        $this->app->bind(ShiftTemplateRepositoryInterface::class, EloquentShiftTemplateRepository::class);
        $this->app->bind(ShiftAssignmentRepositoryInterface::class, EloquentShiftAssignmentRepository::class);
        $this->app->bind(LeaveTypeRepositoryInterface::class, EloquentLeaveTypeRepository::class);
        $this->app->bind(LeavePolicyRepositoryInterface::class, EloquentLeavePolicyRepository::class);
        $this->app->bind(LeaveRequestRepositoryInterface::class, EloquentLeaveRequestRepository::class);
        $this->app->bind(LeaveBalanceRepositoryInterface::class, EloquentLeaveBalanceRepository::class);
        $this->app->bind(LeaveWindowInterface::class, EloquentLeaveWindowRepository::class);
        $this->app->bind(WorkflowTemplateRepositoryInterface::class, EloquentWorkflowTemplateRepository::class);
        $this->app->bind(WorkflowRequestRepositoryInterface::class, EloquentWorkflowRequestRepository::class);
        $this->app->bind(WorkflowDelegationRepositoryInterface::class, EloquentWorkflowDelegationRepository::class);
        $this->app->singleton(ResolverRegistry::class, function () {
            $r = new ResolverRegistry();
            $r->register(new SpecificUserResolver());
            $r->register(new DirectManagerResolver());
            $r->register(new DepartmentHeadResolver());
            $r->register(new RoleResolver(fn (string $roleCode) => \DB::table('users')
                ->join('user_roles', 'user_roles.user_id', '=', 'users.id')
                ->join('roles', 'roles.id', '=', 'user_roles.role_id')
                ->where('roles.code', $roleCode)
                ->whereNull('user_roles.revoked_at')
                ->pluck('users.id')->map(fn ($id) => (string) $id)->toArray()));
            $r->register(new RoleInDepartmentResolver(fn (string $roleCode, string $deptId) => \DB::table('users')
                ->join('user_roles', 'user_roles.user_id', '=', 'users.id')
                ->join('roles', 'roles.id', '=', 'user_roles.role_id')
                ->join('employees', 'employees.user_id', '=', 'users.id')
                ->where('roles.code', $roleCode)
                ->where('employees.department_id', $deptId)
                ->whereNull('user_roles.revoked_at')
                ->pluck('users.id')->map(fn ($id) => (string) $id)->toArray()));
            return $r;
        });
        $this->app->singleton(SubjectDataProviderRegistry::class, function () {
            $p = new SubjectDataProviderRegistry();
            $p->register(new LeaveRequestSubjectProvider(
                app(\App\Modules\Leave\Domain\Repositories\LeaveRequestRepositoryInterface::class),
                app(\App\Modules\Leave\Domain\Repositories\LeaveTypeRepositoryInterface::class),
                app(\App\Modules\Employee\Domain\Repositories\EmployeeRepositoryInterface::class),
            ));
            $p->register(new AttendancePeriodSubjectProvider(
                app(\App\Modules\Attendance\Domain\Repositories\AttendancePeriodRepositoryInterface::class),
            ));
            $p->register(new PayrollPeriodSubjectProvider(
                app(\App\Modules\Payroll\Domain\Repositories\PayrollPeriodRepositoryInterface::class),
            ));
            return $p;
        });
        $this->app->singleton(WorkflowEngine::class);
        $this->commands([\App\Modules\Workflow\Infrastructure\Console\ProcessSlaEscalation::class]);
        $this->app->bind(AttendanceRawLogRepositoryInterface::class, EloquentAttendanceRawLogRepository::class);
        $this->app->bind(AttendanceTimesheetRepositoryInterface::class, EloquentAttendanceTimesheetRepository::class);
        $this->app->bind(AttendanceAdjustmentRequestRepositoryInterface::class, EloquentAttendanceAdjustmentRequestRepository::class);
        $this->app->bind(AttendancePeriodRepositoryInterface::class, EloquentAttendancePeriodRepository::class);
        $this->app->bind(PayrollPeriodRepositoryInterface::class, EloquentPayrollPeriodRepository::class);
        $this->app->bind(PayrollComponentRepositoryInterface::class, EloquentPayrollComponentRepository::class);
        $this->app->bind(PayrollRunRepositoryInterface::class, EloquentPayrollRunRepository::class);
        $this->app->bind(PayrollEntryRepositoryInterface::class, EloquentPayrollEntryRepository::class);
        $this->app->bind(PayrollAdjustmentRepositoryInterface::class, EloquentPayrollAdjustmentRepository::class);
        $this->app->bind(PayslipRepositoryInterface::class, EloquentPayslipRepository::class);
        $this->app->bind(AttendanceReadPort::class, DatabaseAttendanceReadPort::class);
        $this->app->bind(LeaveReadPort::class, DatabaseLeaveReadPort::class);
        $this->app->bind(EmployeeContractReadPort::class, DatabaseEmployeeContractReadPort::class);
        $this->app->bind(\App\Modules\Notification\Domain\Repositories\MessageTemplateRepositoryInterface::class, \App\Modules\Notification\Infrastructure\Persistence\Repositories\EloquentMessageTemplateRepository::class);
        $this->app->bind(\App\Modules\Notification\Domain\Repositories\NotificationMessageRepositoryInterface::class, \App\Modules\Notification\Infrastructure\Persistence\Repositories\EloquentNotificationMessageRepository::class);
        $this->app->bind(\App\Modules\Notification\Domain\Repositories\UserNotificationPreferenceRepositoryInterface::class, \App\Modules\Notification\Infrastructure\Persistence\Repositories\EloquentUserNotificationPreferenceRepository::class);
        $this->app->bind(\App\Modules\Notification\Domain\Repositories\NotificationOutboxRepositoryInterface::class, \App\Modules\Notification\Infrastructure\Persistence\Repositories\EloquentNotificationOutboxRepository::class);
        $this->app->bind(\App\Modules\Notification\Domain\Services\NotificationPublisher::class, \App\Modules\Notification\Application\NotificationPublisherService::class);
        $this->app->bind(\App\Modules\Notification\Infrastructure\Channels\Contracts\NotificationChannelInterface::class . ':in_app', \App\Modules\Notification\Infrastructure\Channels\InAppChannel::class);
        $this->app->bind(\App\Modules\Notification\Infrastructure\Channels\Contracts\NotificationChannelInterface::class . ':email', \App\Modules\Notification\Infrastructure\Channels\EmailChannel::class);
        $this->app->bind(\App\Modules\Notification\Infrastructure\Channels\Contracts\NotificationChannelInterface::class . ':sms', \App\Modules\Notification\Infrastructure\Channels\SmsChannel::class);
        $this->app->bind(\App\Modules\Reporting\Domain\Repositories\ReportDefinitionRepositoryInterface::class, \App\Modules\Reporting\Infrastructure\Persistence\Repositories\EloquentReportDefinitionRepository::class);
        $this->app->bind(\App\Modules\Reporting\Domain\Repositories\ReportRunRepositoryInterface::class, \App\Modules\Reporting\Infrastructure\Persistence\Repositories\EloquentReportRunRepository::class);
        $this->app->bind(\App\Modules\Recruitment\Domain\Repositories\RecruitmentRequisitionRepositoryInterface::class, \App\Modules\Recruitment\Infrastructure\Persistence\Repositories\EloquentRecruitmentRequisitionRepository::class);
        $this->app->bind(\App\Modules\Recruitment\Domain\Repositories\CandidateRepositoryInterface::class, \App\Modules\Recruitment\Infrastructure\Persistence\Repositories\EloquentCandidateRepository::class);
        $this->app->bind(\App\Modules\Recruitment\Domain\Repositories\InterviewRepositoryInterface::class, \App\Modules\Recruitment\Infrastructure\Persistence\Repositories\EloquentInterviewRepository::class);
        $this->app->bind(\App\Modules\Recruitment\Domain\Repositories\OfferRepositoryInterface::class, \App\Modules\Recruitment\Infrastructure\Persistence\Repositories\EloquentOfferRepository::class);
        $this->app->bind(\App\Modules\Onboarding\Domain\Repositories\OnboardingTemplateRepositoryInterface::class, \App\Modules\Onboarding\Infrastructure\Persistence\Repositories\EloquentOnboardingTemplateRepository::class);
        $this->app->bind(\App\Modules\Onboarding\Domain\Repositories\OnboardingPlanRepositoryInterface::class, \App\Modules\Onboarding\Infrastructure\Persistence\Repositories\EloquentOnboardingPlanRepository::class);
                $this->app->bind(\App\Modules\Offboarding\Domain\Repositories\OffboardingRequestRepositoryInterface::class, \App\Modules\Offboarding\Infrastructure\Persistence\Repositories\EloquentOffboardingRequestRepository::class);
        $this->app->bind(\App\Modules\Offboarding\Domain\Repositories\OffboardingPlanRepositoryInterface::class, \App\Modules\Offboarding\Infrastructure\Persistence\Repositories\EloquentOffboardingPlanRepository::class);
        $this->app->bind(\App\Modules\Offboarding\Domain\Repositories\OffboardingTaskRepositoryInterface::class, \App\Modules\Offboarding\Infrastructure\Persistence\Repositories\EloquentOffboardingTaskRepository::class);
        $this->app->bind(\App\Modules\Offboarding\Domain\Repositories\FinalClearanceRepositoryInterface::class, \App\Modules\Offboarding\Infrastructure\Persistence\Repositories\EloquentFinalClearanceRepository::class);
$this->app->bind(\App\Modules\Onboarding\Domain\Repositories\OnboardingTaskRepositoryInterface::class, \App\Modules\Onboarding\Infrastructure\Persistence\Repositories\EloquentOnboardingTaskRepository::class);
        $this->app->bind(\App\Modules\Performance\Domain\Repositories\CompetencyTemplateRepositoryInterface::class, \App\Modules\Performance\Infrastructure\Persistence\Repositories\EloquentCompetencyTemplateRepository::class);
        $this->app->bind(\App\Modules\Performance\Domain\Repositories\GoalRepositoryInterface::class, \App\Modules\Performance\Infrastructure\Persistence\Repositories\EloquentGoalRepository::class);
        $this->app->bind(\App\Modules\Performance\Domain\Repositories\PerformanceCycleRepositoryInterface::class, \App\Modules\Performance\Infrastructure\Persistence\Repositories\EloquentPerformanceCycleRepository::class);
        $this->app->bind(\App\Modules\Performance\Domain\Repositories\PerformanceReviewRepositoryInterface::class, \App\Modules\Performance\Infrastructure\Persistence\Repositories\EloquentPerformanceReviewRepository::class);
        $this->app->bind(\App\Modules\Training\Domain\Repositories\TrainingCourseRepositoryInterface::class, \App\Modules\Training\Infrastructure\Persistence\Repositories\EloquentTrainingCourseRepository::class);
        $this->app->bind(\App\Modules\Training\Domain\Repositories\TrainingSessionRepositoryInterface::class, \App\Modules\Training\Infrastructure\Persistence\Repositories\EloquentTrainingSessionRepository::class);
        $this->app->bind(\App\Modules\Training\Domain\Repositories\TrainingEnrollmentRepositoryInterface::class, \App\Modules\Training\Infrastructure\Persistence\Repositories\EloquentTrainingEnrollmentRepository::class);
        $this->app->bind(\App\Modules\Training\Domain\Repositories\TrainingResultRepositoryInterface::class, \App\Modules\Training\Infrastructure\Persistence\Repositories\EloquentTrainingResultRepository::class);
        $this->commands([\App\Modules\Notification\Infrastructure\Console\ProcessNotificationOutboxCommand::class]);
        $this->app->bind(
            \App\Modules\Asset\Domain\Repositories\AssetItemRepositoryInterface::class,
            \App\Modules\Asset\Infrastructure\Persistence\Eloquent\Repositories\EloquentAssetItemRepository::class,
        );
        $this->app->bind(
            \App\Modules\Asset\Domain\Repositories\AssetAssignmentRepositoryInterface::class,
            \App\Modules\Asset\Infrastructure\Persistence\Eloquent\Repositories\EloquentAssetAssignmentRepository::class,
        );
        $this->app->bind(
            \App\Modules\Asset\Domain\Repositories\AssetReturnRepositoryInterface::class,
            \App\Modules\Asset\Infrastructure\Persistence\Eloquent\Repositories\EloquentAssetReturnRepository::class,
        );
    }

    public function boot(): void
    {
        Event::listen(OfferAccepted::class, CreateEmployeeOnOfferAccepted::class);
        Event::listen(OnboardingPlanCompleted::class, ActivateEmployeeOnOnboardingComplete::class);
        Event::listen(EmployeeStatusChanged::class, CreateOffboardingOnResign::class);

        foreach ([
            UserCreated::class,
            UserLoggedIn::class,
            UserLoginFailed::class,
            UserDisabled::class,
            UserReactivated::class,
            UserPasswordChanged::class,
            UserRoleAssigned::class,
            UserRoleRevoked::class,
            UserDataScopeGranted::class,
            RoleCreated::class,
            RoleUpdated::class,
            RolePermissionGranted::class,
            RolePermissionRevoked::class,
            App\Modules\Configuration\Domain\Events\LookupValueChanged::class,
            App\Modules\Configuration\Domain\Events\CodeGenerationRuleChanged::class,
            App\Modules\Configuration\Domain\Events\SystemSettingChanged::class,
            BranchCreated::class,
            BranchUpdated::class,
            BranchActivated::class,
            BranchDeactivated::class,
            DepartmentCreated::class,
            DepartmentUpdated::class,
            DepartmentMoved::class,
            DepartmentActivated::class,
            DepartmentDeactivated::class,
            PositionCreated::class,
            PositionUpdated::class,
            PositionActivated::class,
            PositionDeactivated::class,
            EmployeeCreated::class,
            EmployeePersonalInfoUpdated::class,
            EmployeeEmploymentChanged::class,
            EmployeeManagerChanged::class,
            EmployeeStatusChanged::class,
            ContractCreated::class,
            ContractActivated::class,
            ContractRenewed::class,
            ContractExpired::class,
            ContractTerminated::class,
            EmployeeDocumentUploaded::class,
            EmployeeDocumentReplaced::class,
            EmployeeDocumentExpired::class,
            EmployeeDocumentArchived::class,
        ] as $event) {
            Event::listen($event, AuditEventListener::class);
        }

        Event::listen(WorkflowApproved::class, SyncLeaveRequestOnWorkflowApproved::class);
        Event::listen(WorkflowRejected::class, SyncLeaveRequestOnWorkflowRejected::class);
    }
}
