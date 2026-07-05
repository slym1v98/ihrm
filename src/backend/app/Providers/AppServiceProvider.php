<?php

namespace App\Providers;

use App\Modules\Asset\Domain\Repositories\AssetAssignmentRepositoryInterface;
use App\Modules\Asset\Domain\Repositories\AssetItemRepositoryInterface;
use App\Modules\Asset\Domain\Repositories\AssetReturnRepositoryInterface;
use App\Modules\Asset\Infrastructure\Persistence\Eloquent\Repositories\EloquentAssetAssignmentRepository;
use App\Modules\Asset\Infrastructure\Persistence\Eloquent\Repositories\EloquentAssetItemRepository;
use App\Modules\Asset\Infrastructure\Persistence\Eloquent\Repositories\EloquentAssetReturnRepository;
use App\Modules\Attendance\Application\Workflow\AttendancePeriodSubjectProvider;
use App\Modules\Attendance\Domain\Repositories\AttendanceAdjustmentRequestRepositoryInterface;
use App\Modules\Attendance\Domain\Repositories\AttendancePeriodRepositoryInterface;
use App\Modules\Attendance\Domain\Repositories\AttendanceRawLogRepositoryInterface;
use App\Modules\Attendance\Domain\Repositories\AttendanceTimesheetRepositoryInterface;
use App\Modules\Attendance\Infrastructure\Persistence\Repositories\EloquentAttendanceAdjustmentRequestRepository;
use App\Modules\Attendance\Infrastructure\Persistence\Repositories\EloquentAttendancePeriodRepository;
use App\Modules\Attendance\Infrastructure\Persistence\Repositories\EloquentAttendanceRawLogRepository;
use App\Modules\Attendance\Infrastructure\Persistence\Repositories\EloquentAttendanceTimesheetRepository;
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
use App\Modules\Employee\Application\Listeners\ActivateEmployeeOnOnboardingComplete;
use App\Modules\Employee\Application\Listeners\CreateOffboardingOnResign;
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
use App\Modules\Employee\Domain\Repositories\ContractRepositoryInterface;
use App\Modules\Employee\Domain\Repositories\EmployeeDocumentRepositoryInterface;
use App\Modules\Employee\Domain\Repositories\EmployeeRepositoryInterface;
use App\Modules\Employee\Infrastructure\Persistence\Repositories\EloquentContractRepository;
use App\Modules\Employee\Infrastructure\Persistence\Repositories\EloquentEmployeeDocumentRepository;
use App\Modules\Employee\Infrastructure\Persistence\Repositories\EloquentEmployeeRepository;
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
use App\Modules\Identity\Domain\Repositories\RoleRepositoryInterface;
use App\Modules\Identity\Domain\Repositories\UserRepositoryInterface;
use App\Modules\Identity\Infrastructure\Persistence\Repositories\EloquentRoleRepository;
use App\Modules\Identity\Infrastructure\Persistence\Repositories\EloquentUserRepository;
use App\Modules\Leave\Application\Workflow\LeaveRequestSubjectProvider;
use App\Modules\Leave\Application\Workflow\Listeners\SyncLeaveRequestOnWorkflowApproved;
use App\Modules\Leave\Application\Workflow\Listeners\SyncLeaveRequestOnWorkflowRejected;
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
use App\Modules\Notification\Application\NotificationPublisherService;
use App\Modules\Notification\Domain\Repositories\MessageTemplateRepositoryInterface;
use App\Modules\Notification\Domain\Repositories\NotificationMessageRepositoryInterface;
use App\Modules\Notification\Domain\Repositories\NotificationOutboxRepositoryInterface;
use App\Modules\Notification\Domain\Repositories\UserNotificationPreferenceRepositoryInterface;
use App\Modules\Notification\Domain\Services\NotificationPublisher;
use App\Modules\Notification\Infrastructure\Channels\Contracts\NotificationChannelInterface;
use App\Modules\Notification\Infrastructure\Channels\EmailChannel;
use App\Modules\Notification\Infrastructure\Channels\InAppChannel;
use App\Modules\Notification\Infrastructure\Channels\SmsChannel;
use App\Modules\Notification\Infrastructure\Console\ProcessNotificationOutboxCommand;
use App\Modules\Notification\Infrastructure\Persistence\Repositories\EloquentMessageTemplateRepository;
use App\Modules\Notification\Infrastructure\Persistence\Repositories\EloquentNotificationMessageRepository;
use App\Modules\Notification\Infrastructure\Persistence\Repositories\EloquentNotificationOutboxRepository;
use App\Modules\Notification\Infrastructure\Persistence\Repositories\EloquentUserNotificationPreferenceRepository;
use App\Modules\Offboarding\Domain\Repositories\FinalClearanceRepositoryInterface;
use App\Modules\Offboarding\Domain\Repositories\OffboardingPlanRepositoryInterface;
use App\Modules\Offboarding\Domain\Repositories\OffboardingRequestRepositoryInterface;
use App\Modules\Offboarding\Domain\Repositories\OffboardingTaskRepositoryInterface;
use App\Modules\Offboarding\Infrastructure\Persistence\Repositories\EloquentFinalClearanceRepository;
use App\Modules\Offboarding\Infrastructure\Persistence\Repositories\EloquentOffboardingPlanRepository;
use App\Modules\Offboarding\Infrastructure\Persistence\Repositories\EloquentOffboardingRequestRepository;
use App\Modules\Offboarding\Infrastructure\Persistence\Repositories\EloquentOffboardingTaskRepository;
use App\Modules\Onboarding\Domain\Events\OnboardingPlanCompleted;
use App\Modules\Onboarding\Domain\Repositories\OnboardingPlanRepositoryInterface;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTaskRepositoryInterface;
use App\Modules\Onboarding\Domain\Repositories\OnboardingTemplateRepositoryInterface;
use App\Modules\Onboarding\Infrastructure\Persistence\Repositories\EloquentOnboardingPlanRepository;
use App\Modules\Onboarding\Infrastructure\Persistence\Repositories\EloquentOnboardingTaskRepository;
use App\Modules\Onboarding\Infrastructure\Persistence\Repositories\EloquentOnboardingTemplateRepository;
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
use App\Modules\Organization\Domain\Repositories\BranchRepositoryInterface;
use App\Modules\Organization\Domain\Repositories\DepartmentRepositoryInterface;
use App\Modules\Organization\Domain\Repositories\PositionRepositoryInterface;
use App\Modules\Organization\Infrastructure\Persistence\Repositories\EloquentBranchRepository;
use App\Modules\Organization\Infrastructure\Persistence\Repositories\EloquentDepartmentRepository;
use App\Modules\Organization\Infrastructure\Persistence\Repositories\EloquentPositionRepository;
use App\Modules\Payroll\Application\Workflow\PayrollPeriodSubjectProvider;
use App\Modules\Payroll\Domain\Ports\AttendanceReadPort;
use App\Modules\Payroll\Domain\Ports\EmployeeContractReadPort;
use App\Modules\Payroll\Domain\Ports\LeaveReadPort;
use App\Modules\Payroll\Domain\Repositories\PayrollAdjustmentRepositoryInterface;
use App\Modules\Payroll\Domain\Repositories\PayrollComponentRepositoryInterface;
use App\Modules\Payroll\Domain\Repositories\PayrollEntryRepositoryInterface;
use App\Modules\Payroll\Domain\Repositories\PayrollPeriodRepositoryInterface;
use App\Modules\Payroll\Domain\Repositories\PayrollRunRepositoryInterface;
use App\Modules\Payroll\Domain\Repositories\PayslipRepositoryInterface;
use App\Modules\Payroll\Infrastructure\Persistence\Repositories\EloquentPayrollAdjustmentRepository;
use App\Modules\Payroll\Infrastructure\Persistence\Repositories\EloquentPayrollComponentRepository;
use App\Modules\Payroll\Infrastructure\Persistence\Repositories\EloquentPayrollEntryRepository;
use App\Modules\Payroll\Infrastructure\Persistence\Repositories\EloquentPayrollPeriodRepository;
use App\Modules\Payroll\Infrastructure\Persistence\Repositories\EloquentPayrollRunRepository;
use App\Modules\Payroll\Infrastructure\Persistence\Repositories\EloquentPayslipRepository;
use App\Modules\Payroll\Infrastructure\Ports\DatabaseAttendanceReadPort;
use App\Modules\Payroll\Infrastructure\Ports\DatabaseEmployeeContractReadPort;
use App\Modules\Payroll\Infrastructure\Ports\DatabaseLeaveReadPort;
use App\Modules\Performance\Domain\Repositories\CompetencyTemplateRepositoryInterface;
use App\Modules\Performance\Domain\Repositories\GoalRepositoryInterface;
use App\Modules\Performance\Domain\Repositories\PerformanceCycleRepositoryInterface;
use App\Modules\Performance\Domain\Repositories\PerformanceReviewRepositoryInterface;
use App\Modules\Performance\Infrastructure\Persistence\Repositories\EloquentCompetencyTemplateRepository;
use App\Modules\Performance\Infrastructure\Persistence\Repositories\EloquentGoalRepository;
use App\Modules\Performance\Infrastructure\Persistence\Repositories\EloquentPerformanceCycleRepository;
use App\Modules\Performance\Infrastructure\Persistence\Repositories\EloquentPerformanceReviewRepository;
use App\Modules\Recruitment\Application\Listeners\CreateEmployeeOnOfferAccepted;
use App\Modules\Recruitment\Domain\Events\OfferAccepted;
use App\Modules\Recruitment\Domain\Repositories\CandidateRepositoryInterface;
use App\Modules\Recruitment\Domain\Repositories\InterviewRepositoryInterface;
use App\Modules\Recruitment\Domain\Repositories\OfferRepositoryInterface;
use App\Modules\Recruitment\Domain\Repositories\RecruitmentRequisitionRepositoryInterface;
use App\Modules\Recruitment\Infrastructure\Persistence\Repositories\EloquentCandidateRepository;
use App\Modules\Recruitment\Infrastructure\Persistence\Repositories\EloquentInterviewRepository;
use App\Modules\Recruitment\Infrastructure\Persistence\Repositories\EloquentOfferRepository;
use App\Modules\Recruitment\Infrastructure\Persistence\Repositories\EloquentRecruitmentRequisitionRepository;
use App\Modules\Reporting\Domain\Repositories\ReportDefinitionRepositoryInterface;
use App\Modules\Reporting\Domain\Repositories\ReportRunRepositoryInterface;
use App\Modules\Reporting\Infrastructure\Persistence\Repositories\EloquentReportDefinitionRepository;
use App\Modules\Reporting\Infrastructure\Persistence\Repositories\EloquentReportRunRepository;
use App\Modules\Shift\Domain\Repositories\ShiftAssignmentRepositoryInterface;
use App\Modules\Shift\Domain\Repositories\ShiftTemplateRepositoryInterface;
use App\Modules\Shift\Infrastructure\Persistence\Repositories\EloquentShiftAssignmentRepository;
use App\Modules\Shift\Infrastructure\Persistence\Repositories\EloquentShiftTemplateRepository;
use App\Modules\Training\Domain\Repositories\TrainingCourseRepositoryInterface;
use App\Modules\Training\Domain\Repositories\TrainingEnrollmentRepositoryInterface;
use App\Modules\Training\Domain\Repositories\TrainingResultRepositoryInterface;
use App\Modules\Training\Domain\Repositories\TrainingSessionRepositoryInterface;
use App\Modules\Training\Infrastructure\Persistence\Repositories\EloquentTrainingCourseRepository;
use App\Modules\Training\Infrastructure\Persistence\Repositories\EloquentTrainingEnrollmentRepository;
use App\Modules\Training\Infrastructure\Persistence\Repositories\EloquentTrainingResultRepository;
use App\Modules\Training\Infrastructure\Persistence\Repositories\EloquentTrainingSessionRepository;
use App\Modules\Workflow\Application\Resolvers\DepartmentHeadResolver;
use App\Modules\Workflow\Application\Resolvers\DirectManagerResolver;
use App\Modules\Workflow\Application\Resolvers\RoleInDepartmentResolver;
use App\Modules\Workflow\Application\Resolvers\RoleResolver;
use App\Modules\Workflow\Application\Resolvers\SpecificUserResolver;
use App\Modules\Workflow\Application\Services\ResolverRegistry;
use App\Modules\Workflow\Application\Services\SubjectDataProviderRegistry;
use App\Modules\Workflow\Application\Services\WorkflowEngine;
use App\Modules\Workflow\Domain\Events\WorkflowApproved;
use App\Modules\Workflow\Domain\Events\WorkflowRejected;
use App\Modules\Workflow\Domain\Repositories\WorkflowDelegationRepositoryInterface;
use App\Modules\Workflow\Domain\Repositories\WorkflowRequestRepositoryInterface;
use App\Modules\Workflow\Domain\Repositories\WorkflowTemplateRepositoryInterface;
use App\Modules\Workflow\Infrastructure\Console\ProcessSlaEscalation;
use App\Modules\Workflow\Infrastructure\Persistence\Repositories\EloquentWorkflowDelegationRepository;
use App\Modules\Workflow\Infrastructure\Persistence\Repositories\EloquentWorkflowRequestRepository;
use App\Modules\Workflow\Infrastructure\Persistence\Repositories\EloquentWorkflowTemplateRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
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
            $r = new ResolverRegistry;
            $r->register(new SpecificUserResolver);
            $r->register(new DirectManagerResolver);
            $r->register(new DepartmentHeadResolver);
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
            $p = new SubjectDataProviderRegistry;
            $p->register(new LeaveRequestSubjectProvider(
                app(LeaveRequestRepositoryInterface::class),
                app(LeaveTypeRepositoryInterface::class),
                app(EmployeeRepositoryInterface::class),
            ));
            $p->register(new AttendancePeriodSubjectProvider(
                app(AttendancePeriodRepositoryInterface::class),
            ));
            $p->register(new PayrollPeriodSubjectProvider(
                app(PayrollPeriodRepositoryInterface::class),
            ));

            return $p;
        });
        $this->app->singleton(WorkflowEngine::class);
        $this->commands([ProcessSlaEscalation::class]);
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
        $this->app->bind(MessageTemplateRepositoryInterface::class, EloquentMessageTemplateRepository::class);
        $this->app->bind(NotificationMessageRepositoryInterface::class, EloquentNotificationMessageRepository::class);
        $this->app->bind(UserNotificationPreferenceRepositoryInterface::class, EloquentUserNotificationPreferenceRepository::class);
        $this->app->bind(NotificationOutboxRepositoryInterface::class, EloquentNotificationOutboxRepository::class);
        $this->app->bind(NotificationPublisher::class, NotificationPublisherService::class);
        $this->app->bind(NotificationChannelInterface::class.':in_app', InAppChannel::class);
        $this->app->bind(NotificationChannelInterface::class.':email', EmailChannel::class);
        $this->app->bind(NotificationChannelInterface::class.':sms', SmsChannel::class);
        $this->app->bind(ReportDefinitionRepositoryInterface::class, EloquentReportDefinitionRepository::class);
        $this->app->bind(ReportRunRepositoryInterface::class, EloquentReportRunRepository::class);
        $this->app->bind(RecruitmentRequisitionRepositoryInterface::class, EloquentRecruitmentRequisitionRepository::class);
        $this->app->bind(CandidateRepositoryInterface::class, EloquentCandidateRepository::class);
        $this->app->bind(InterviewRepositoryInterface::class, EloquentInterviewRepository::class);
        $this->app->bind(OfferRepositoryInterface::class, EloquentOfferRepository::class);
        $this->app->bind(OnboardingTemplateRepositoryInterface::class, EloquentOnboardingTemplateRepository::class);
        $this->app->bind(OnboardingPlanRepositoryInterface::class, EloquentOnboardingPlanRepository::class);
        $this->app->bind(OffboardingRequestRepositoryInterface::class, EloquentOffboardingRequestRepository::class);
        $this->app->bind(OffboardingPlanRepositoryInterface::class, EloquentOffboardingPlanRepository::class);
        $this->app->bind(OffboardingTaskRepositoryInterface::class, EloquentOffboardingTaskRepository::class);
        $this->app->bind(FinalClearanceRepositoryInterface::class, EloquentFinalClearanceRepository::class);
        $this->app->bind(OnboardingTaskRepositoryInterface::class, EloquentOnboardingTaskRepository::class);
        $this->app->bind(CompetencyTemplateRepositoryInterface::class, EloquentCompetencyTemplateRepository::class);
        $this->app->bind(GoalRepositoryInterface::class, EloquentGoalRepository::class);
        $this->app->bind(PerformanceCycleRepositoryInterface::class, EloquentPerformanceCycleRepository::class);
        $this->app->bind(PerformanceReviewRepositoryInterface::class, EloquentPerformanceReviewRepository::class);
        $this->app->bind(TrainingCourseRepositoryInterface::class, EloquentTrainingCourseRepository::class);
        $this->app->bind(TrainingSessionRepositoryInterface::class, EloquentTrainingSessionRepository::class);
        $this->app->bind(TrainingEnrollmentRepositoryInterface::class, EloquentTrainingEnrollmentRepository::class);
        $this->app->bind(TrainingResultRepositoryInterface::class, EloquentTrainingResultRepository::class);
        $this->commands([ProcessNotificationOutboxCommand::class]);
        $this->app->bind(
            AssetItemRepositoryInterface::class,
            EloquentAssetItemRepository::class,
        );
        $this->app->bind(
            AssetAssignmentRepositoryInterface::class,
            EloquentAssetAssignmentRepository::class,
        );
        $this->app->bind(
            AssetReturnRepositoryInterface::class,
            EloquentAssetReturnRepository::class,
        );
    }

    public function boot(): void
    {
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('auth', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('strict', fn (Request $request) => Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()));
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
