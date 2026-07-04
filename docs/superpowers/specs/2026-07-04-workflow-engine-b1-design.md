# B1 Design — Workflow Engine nâng cao

Ngày: 2026-07-04
Phạm vi: Nhóm B1 — `Workflow Engine` nâng cao
Trạng thái: Draft đã được user duyệt ở mức thiết kế

## 1. Mục tiêu

Triển khai lớp workflow dùng chung cho các module nghiệp vụ với các khả năng trong B1:

- Conditional routing động theo context nghiệp vụ
- Assignee resolver mở rộng, plug được theo module
- Delegation (ủy quyền phê duyệt) theo khoảng thời gian
- Integration pattern chuẩn cho module nghiệp vụ
- Áp dụng integration thật cho `Leave` làm reference module

Ngoài phạm vi B1:

- Parallel step / all-of / any-of
- SLA / timeout / auto escalation
- Tích hợp thật ngay cho Attendance và Payroll

## 2. Hiện trạng

Module `Workflow` hiện đã có:

- `WorkflowTemplate` với danh sách `WorkflowStep`
- `WorkflowRequest` với các transition: submit, approve, reject, return_for_edit, cancel
- Bảng persistence cho template, request, actions
- HTTP API cho create/list/show/approve/reject/return/cancel

Giới hạn hiện tại:

- `condition` mới chỉ lưu, chưa có engine evaluate
- `assignee_type` mới có cấu trúc dữ liệu, chưa có resolver registry đủ mạnh
- Không có delegation
- Không có contract chuẩn cho module nghiệp vụ cung cấp subject context
- Leave/Attendance/Payroll chưa tích hợp theo pattern chung

## 3. Mục tiêu kiến trúc

Thiết kế phải đạt:

- Handler mỏng, logic routing nằm trong service riêng
- Không coupling ngược từ `Workflow` sang `Leave`
- Dễ test từng phần độc lập: evaluator, resolver, delegation, integration
- Có thể mở rộng thêm resolver mới mà không sửa core engine
- Có thể thêm Attendance/Payroll sau bằng đúng contract đã có

## 4. Phương án được chọn

Chọn **Approach 2: WorkflowEngine Service**.

Lý do:

- Gọn hơn event-driven full state machine
- Ít rủi ro hơn việc nhét logic vào handler
- Tạo được plugin points rõ ràng cho resolver / subject provider / delegation
- Phù hợp YAGNI cho scope B1

## 5. Kiến trúc tổng thể

### 5.1. Thành phần mới

- `WorkflowEngine`
- `ConditionEvaluator`
- `ResolverRegistry`
- `DelegationResolver`
- `SubjectDataProviderRegistry`
- `LeaveRequestSubjectProvider`
- `WorkflowDelegation` aggregate/model/repository/API

### 5.2. Luồng submit request

1. Module nghiệp vụ tạo subject chính, ví dụ `LeaveRequest`
2. Module gọi `SubmitWorkflowRequestHandler`
3. `SubmitWorkflowRequestHandler`:
   - load template
   - lấy `SubjectDataProvider` theo `subject_type`
   - fetch context theo `subject_id`
   - tạo `WorkflowRequest` với `context snapshot`
   - gọi `WorkflowEngine.advanceFromSubmit(...)`
4. Engine resolve first active step
5. Engine evaluate condition + resolve approvers + apply delegation
6. Lưu request + action metadata

### 5.3. Luồng approve step

1. `ApproveWorkflowStepHandler` load request + template
2. Domain aggregate ghi action approve step hiện tại
3. Handler gọi `WorkflowEngine.advanceAfterApproval(...)`
4. Engine:
   - tìm step tiếp theo theo thứ tự
   - evaluate `condition`
   - nếu false → skip step và lặp tiếp
   - nếu không còn step → mark request approved
   - nếu còn step hợp lệ → resolve approvers + delegation
5. Save request
6. Dispatch event workflow tương ứng

## 6. Dữ liệu và schema

## 6.1. Thay đổi bảng hiện có

### `workflow_template_steps`

Giữ các cột hiện có, nhưng đổi ý nghĩa runtime như sau:

- `assignee_type`: backward-compatible, nhưng B1 sẽ dần ưu tiên `resolver_type`
- thêm `resolver_type varchar(40)`
- thêm `resolver_config jsonb default '{}'`
- giữ `condition jsonb nullable`

Ví dụ:

```json
{
  "resolver_type": "direct_manager",
  "resolver_config": {}
}
```

hoặc:

```json
{
  "resolver_type": "role",
  "resolver_config": {"role_code": "hr_manager"}
}
```

### `workflow_requests`

Thêm:

- `context jsonb nullable`

Ý nghĩa:

- Snapshot subject context tại thời điểm submit
- Mặc định B1 sẽ dùng snapshot immutable
- Có thể mở rộng sau bằng `refresh_on_step` trong B1.5 nếu cần

### `workflow_request_actions`

Thêm:

- `resolved_approvers jsonb default '[]'`
- `delegation_map jsonb default '{}'`

Ý nghĩa:

- `resolved_approvers`: danh sách user thực tế được yêu cầu approve ở step đó
- `delegation_map`: map `delegator_id -> delegate_id`

## 6.2. Bảng mới: `workflow_delegations`

Cột:

- `id uuid primary`
- `delegator_id uuid`
- `delegate_id uuid`
- `role_type varchar(30) nullable`
- `start_at timestamp`
- `end_at timestamp`
- `active boolean default true`
- `created_by uuid nullable`
- `created_at timestamp`
- `updated_at timestamp`

Rule:

- Không FK cứng sang bảng users để giảm coupling ở level migration/runtime
- Query active delegation theo `delegator_id + active + time window`

Index:

- `(delegator_id, active)`
- `(start_at, end_at)`

## 7. WorkflowEngine Service

## 7.1. Interface logic

```php
final class WorkflowEngine
{
    public function advanceFromSubmit(WorkflowRequest $request, WorkflowTemplate $template): void;

    public function advanceAfterApproval(WorkflowRequest $request, WorkflowTemplate $template): void;
}
```

Engine chịu trách nhiệm:

- Chọn next valid step
- Skip step fail condition
- Resolve approvers
- Apply delegation
- Ghi metadata để audit
- Chốt approved nếu hết step

Không chịu trách nhiệm:

- Persist DB trực tiếp
- Gọi controller
- Gọi thẳng logic Leave/Attendance/Payroll

## 7.2. Thuật toán next step

Pseudo:

```php
$current = $request->currentStep();
$next = $template->nextStepAfter($current);
while ($next !== null) {
    if ($conditionEvaluator->evaluate($next->condition(), $context)) {
        $resolved = $resolverRegistry->get($next->resolverType())->resolve($next->resolverConfig(), $context);
        $delegated = $delegationResolver->resolve($resolved, now());
        $request->moveToStep($next->stepOrder(), $delegated->effectiveApproverIds, $delegated->delegationMap);
        return;
    }
    $next = $template->nextStepAfter($next->stepOrder());
}
$request->markApproved();
```

## 8. Condition Evaluator

## 8.1. DSL

Condition dùng JSON DSL đơn giản.

Ví dụ:

```json
{
  "op": "and",
  "conditions": [
    { "field": "duration_days", "op": ">=", "value": 3 },
    { "field": "leave_type_code", "op": "in", "value": ["annual", "sick"] }
  ]
}
```

## 8.2. Toán tử hỗ trợ B1

Logical:

- `and`
- `or`
- `not`

Comparison:

- `eq`
- `neq`
- `gt`
- `gte`
- `lt`
- `lte`
- `in`
- `nin`
- `exists`

Để tránh ambiguity giữa symbol và keyword, runtime sẽ normalize:

- `=` → `eq`
- `!=` → `neq`
- `>` → `gt`
- `>=` → `gte`
- `<` → `lt`
- `<=` → `lte`

## 8.3. Signature

```php
final class ConditionEvaluator
{
    public function evaluate(?array $condition, array $context): bool;
}
```

Rule:

- `condition === null` → `true`
- Pure function, không query DB
- Nếu field không tồn tại trong context:
  - `exists` trả `false`
  - các op khác trả `false`

## 9. Resolver Registry

## 9.1. Interface

```php
interface AssigneeResolver
{
    public function key(): string;

    /** @return string[] user IDs */
    public function resolve(array $config, array $context): array;
}
```

## 9.2. Built-in resolver B1

- `specific_user`
- `role`
- `direct_manager`
- `department_head`
- `role_in_department`

### `specific_user`

Config:

```json
{ "user_id": "...uuid..." }
```

### `role`

Config:

```json
{ "role_code": "hr_manager" }
```

### `direct_manager`

Config rỗng, resolve từ `context.manager_id`

### `department_head`

Resolve từ `context.department_head_user_id`

### `role_in_department`

Config:

```json
{ "role_code": "hr_staff", "department_scope": "subject_department" }
```

## 9.3. Registry

```php
final class ResolverRegistry
{
    public function register(AssigneeResolver $resolver): void;

    public function get(string $key): AssigneeResolver;
}
```

Đăng ký tại service provider.

Quy tắc mở rộng:

- Workflow core chỉ biết interface
- Module khác có thể `register()` thêm resolver riêng
- Nếu key không tồn tại → throw domain/application exception rõ ràng

## 10. SubjectDataProvider pattern

## 10.1. Interface

```php
interface SubjectDataProvider
{
    public function subjectType(): string;

    public function fetchContext(string $subjectId): array;
}
```

## 10.2. Registry

Tương tự resolver registry:

```php
final class SubjectDataProviderRegistry
{
    public function register(SubjectDataProvider $provider): void;

    public function get(string $subjectType): SubjectDataProvider;
}
```

## 10.3. Leave reference provider

`LeaveRequestSubjectProvider` trả về context tối thiểu:

```php
[
  'subject_type' => 'leave_request',
  'subject_id' => '...',
  'employee_id' => '...',
  'manager_id' => '...',
  'department_id' => '...',
  'department_head_user_id' => '...',
  'leave_type_id' => '...',
  'leave_type_code' => 'annual',
  'duration_days' => 5,
  'duration_minutes' => 2400,
  'start_at' => '2026-07-05',
  'end_at' => '2026-07-09'
]
```

Rule:

- Context đủ để evaluate điều kiện và resolve approver
- Không trả object domain, chỉ array scalar/serializable

## 11. Delegation Resolver

## 11.1. Logic

Input: danh sách approver IDs gốc

Output:

- `effectiveApproverIds`
- `delegationMap`

```php
final class DelegationResult
{
    public function __construct(
        public array $effectiveApproverIds,
        public array $delegationMap,
    ) {}
}
```

## 11.2. Rule nghiệp vụ

- Chỉ hỗ trợ 1 lớp delegation
- Nhiều delegation active cùng lúc cho cùng delegator:
  - lấy record mới nhất theo `created_at desc`
- Delegate không được chain tiếp trong cùng resolution pass
- Nếu delegation hết hạn hoặc inactive → ignore
- Audit luôn lưu người gốc và người thay thế

## 11.3. Xử lý conflict

- Nếu delegate == delegator → reject khi tạo delegation
- Nếu `start_at >= end_at` → validation error
- Nếu tạo delegation overlap cùng delegator và cùng `role_type`:
  - reject để tránh ambiguity trong B1

## 12. API B1 mới

## 12.1. Delegations

### POST `/v1/workflow-delegations`

Payload:

```json
{
  "delegate_id": "...",
  "role_type": "workflow_approver",
  "start_at": "2026-07-05 08:00:00",
  "end_at": "2026-07-10 18:00:00"
}
```

Mặc định `delegator_id` lấy từ authenticated user.

### GET `/v1/workflow-delegations`

Filter:

- `active`
- `mine`
- `delegator_id` (admin only nếu cần)

### DELETE `/v1/workflow-delegations/{id}`

Soft revoke logic:

- set `active = false`
- không hard delete để giữ audit

## 12.2. Template payload mới

Khi tạo workflow template, mỗi step chấp nhận:

```json
{
  "step_order": 1,
  "name": "Manager approval",
  "resolver_type": "direct_manager",
  "resolver_config": {},
  "condition": null
}
```

B1 sẽ vẫn map backward-compatible nếu payload cũ gửi `assignee_type/assignee_id`.

## 13. Integration với Leave (reference module)

## 13.1. Mục tiêu

Chứng minh integration pattern hoạt động end-to-end nhưng không làm phình scope B1.

## 13.2. Cách tích hợp

- `LeaveType` hoặc config tương đương có `workflow_template_code nullable`
- Nếu `workflow_template_code` có giá trị:
  - submit leave → tạo `LeaveRequest` pending
  - sau đó create `WorkflowRequest`
- Workflow event → listener trong module Leave
- Listener gọi command handler Leave hiện có

## 13.3. Event listeners

- `WorkflowApproved` → `SyncLeaveRequestOnApproval`
- `WorkflowRejected` → `SyncLeaveRequestOnRejection`
- `WorkflowReturnedForEdit` → `SyncLeaveRequestOnReturnForEdit` (nếu Leave có trạng thái tương ứng; nếu chưa có thì B1 chỉ xử lý approve/reject)
- `WorkflowCancelled` → tùy chọn, không bắt buộc cho leave B1 nếu leave request vẫn do người submit tự cancel ở module Leave

## 13.4. Mapping rule

Chỉ xử lý event nếu:

```php
$workflowRequest->subjectType() === 'leave_request'
```

Listener phải load `WorkflowRequest` hoặc event payload đủ dữ liệu để biết `subject_id`.

## 13.5. Trạng thái Leave

B1 giữ logic hiện có của Leave, chỉ thay đổi trigger:

- `approve` leave qua workflow → gọi `ApproveLeaveRequestHandler`
- `reject` leave qua workflow → gọi `RejectLeaveRequestHandler`

Balance deduction vẫn chỉ xảy ra trong `ApproveLeaveRequestHandler`, không chuyển sang workflow core.

## 14. Error handling

Cần thêm exception rõ ràng cho B1:

- `WorkflowResolverNotFoundException`
- `WorkflowSubjectProviderNotFoundException`
- `WorkflowDelegationConflictException`
- `WorkflowDelegationNotFoundException`
- `WorkflowConditionEvaluationException` (chỉ dùng khi DSL malformed; condition false không phải exception)

Response message phải detail, không generic.

Ví dụ:

- `Không tìm thấy assignee resolver: direct_manager`
- `Không thể tạo ủy quyền bị chồng thời gian cho cùng người ủy quyền`
- `Không tìm thấy subject provider cho loại leave_request`

## 15. Testing strategy

## 15.1. Unit tests

Thêm test cho:

- `ConditionEvaluator`
  - and/or/not
  - comparison ops
  - null condition
  - missing field
- `DelegationResolver`
  - active delegation
  - expired delegation
  - overlap rejection
  - no chain delegation
- `ResolverRegistry`
  - register + get
  - unknown resolver
- built-in resolvers
  - `specific_user`
  - `direct_manager`
  - `department_head`

## 15.2. Feature tests

Workflow module:

- submit request với template có 3 bước, step giữa bị skip bởi condition
- approve step → next step resolve đúng approver
- approve step khi có active delegation → delegate là approver hiệu lực
- reject request → status final đúng
- API create/list/revoke delegation

Leave integration:

- submit leave có workflow template
- approve workflow request → leave approved + balance deducted
- reject workflow request → leave rejected

## 15.3. Auth / permission boundary

Theo convention repo, phải có feature test permission boundary cho API mới:

- create delegation không auth → 401
- revoke delegation không phải owner/admin → 403
- approve workflow request không đúng approver hiệu lực → 403 hoặc domain exception mapping phù hợp

## 16. Boundary và YAGNI

Cố tình chưa làm trong B1:

- Parallel approvals
- Quorum / all-of / any-of
- Escalation theo SLA
- Reminder/notification engine
- UI builder phức tạp cho JSON DSL
- Attendance/Payroll integration thật

Ceiling hiện tại:

- Một step = một resolver → một danh sách approver hiệu lực
- Sequence approval only
- Snapshot context immutable khi submit

Upgrade path:

- B1.5 thêm `approval_mode`, `sla_hours`, `refresh_on_step`
- B2/B3 thêm provider cho Attendance/Payroll theo đúng contract hiện có

## 17. File/Module tác động dự kiến

### Workflow

- `Application/CommandHandlers/*`
- `Domain/Aggregates/WorkflowRequest/*`
- `Domain/Aggregates/WorkflowTemplate/*`
- `Domain/Exceptions/*`
- `Infrastructure/Http/Controllers/*`
- `Infrastructure/Http/Requests/*`
- `Infrastructure/Persistence/*`
- new service provider / service classes dưới `Application` hoặc `Domain/Services`

### Leave

- provider mới cho workflow context
- event listeners cho approve/reject
- có thể thêm config field `workflow_template_code`

### Database

- migration add columns tables workflow hiện có
- migration create `workflow_delegations`

## 18. Acceptance criteria

### AC1
Template step có thể cấu hình `resolver_type`, `resolver_config`, `condition`.

### AC2
Workflow submit snapshot được `context` của subject.

### AC3
Approve step sẽ skip các step có condition false và tới step hợp lệ tiếp theo.

### AC4
Built-in resolvers hoạt động: `specific_user`, `role`, `direct_manager`, `department_head`, `role_in_department`.

### AC5
Delegation active làm thay đổi approver hiệu lực nhưng vẫn lưu audit người gốc.

### AC6
Không cho phép delegation overlap cho cùng delegator + role_type trong B1.

### AC7
Leave có thể dùng workflow engine qua integration pattern chuẩn.

### AC8
Approve workflow của leave sẽ dẫn tới approve leave thực sự và trừ balance đúng một lần.

### AC9
Reject workflow của leave sẽ reject leave request.

### AC10
Full backend test suite vẫn pass sau khi triển khai.

## 19. Rủi ro và lưu ý

- Resolver kiểu `role` và `role_in_department` phụ thuộc chất lượng dữ liệu identity/organization hiện có; nếu mapping role-user-department chưa chuẩn thì cần thêm adapter đọc dữ liệu.
- Snapshot context giúp stable workflow nhưng có thể stale nếu org hierarchy đổi giữa chừng; đây là tradeoff chấp nhận trong B1.
- Leave integration phải tránh double-approve nếu listener bị gọi lặp; cần idempotency guard tại listener hoặc aggregate transition.

## 20. Kết luận

B1 sẽ biến module Workflow từ CRUD/approval tuyến tính thành engine có khả năng:

- route theo điều kiện
- resolve approver động
- hỗ trợ ủy quyền
- tích hợp chuẩn với module nghiệp vụ

Mức triển khai được giữ vừa đủ để `Leave` dùng thật, trong khi không mở rộng quá sớm sang parallel step hay SLA.
