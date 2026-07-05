# Quality & Reliability Improvement Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Improve backend code quality through static analysis, consistent error handling, and validation coverage.

**Architecture:** Add tooling (PHPStan/Pint) with CI enforcement, refactor exception rendering for consistent JSON API errors, and add Form Request validation for all modules. Each area is independent but sequenced to fix foundation first (tools → errors → validation).

**Tech Stack:** Laravel 12, PHP 8.2+, PHPStan/Larastan, Laravel Pint, GitHub Actions

---

### Task 1: Add PHPStan / Larastan

**Files:**
- Modify: `src/backend/composer.json`
- Create: `src/backend/phpstan.neon`
- Modify: `.github/workflows/ci.yml`

- [ ] **Step 1: Install larastan**

```bash
cd src/backend && composer require --dev larastan/larastan:^3.0
```

- [ ] **Step 2: Create phpstan.neon**

```neon
includes:
    - vendor/larastan/larastan/extension.neon

parameters:
    level: 5
    paths:
        - app/Modules
    excludePaths:
        - database/migrations/*
        - config/*
    checkUnusedIgnoredErrors: true
    reportUnmatchedIgnoredErrors: true
    databaseMigrationsPath: database/migrations
```

- [ ] **Step 3: Add composer scripts**

```json
"scripts": {
    "analyse": "phpstan analyse --memory-limit=2G",
    "analyse:ci": "phpstan analyse --no-progress --error-format=github"
}
```

Edit `src/backend/composer.json` scripts section.

- [ ] **Step 4: Run analyse and fix initial errors**

```bash
cd src/backend && php vendor/bin/phpstan analyse --level=5 --memory-limit=2G app/Modules
```

Fix any errors found. Likely issues:
- Missing return types on methods
- Unused constructor params
- Wrong docblock types

- [ ] **Step 5: Re-run to confirm zero errors**

```bash
cd src/backend && php vendor/bin/phpstan analyse --level=5 --memory-limit=2G
```
Expected: `[OK] No errors`

- [ ] **Step 6: Commit**

```bash
git add src/backend/composer.json src/backend/composer.lock src/backend/phpstan.neon
git commit -m "feat(quality): add PHPStan/Larastan level 5 with config"
```

---

### Task 2: Configure Laravel Pint

**Files:**
- Create: `src/backend/pint.json`
- Modify: `src/backend/composer.json`

- [ ] **Step 1: Create pint.json**

```json
{
    "preset": "laravel",
    "rules": {
        "ordered_imports": {
            "sort_algorithm": "alpha"
        },
        "no_unused_imports": true,
        "single_quote": true,
        "trailing_comma_in_multiline": true
    }
}
```

- [ ] **Step 2: Add composer scripts**

```json
"lint": "pint",
"lint:test": "pint --test"
```

Already in scripts section from Task 1.

- [ ] **Step 3: Run lint and fix**

```bash
cd src/backend && php vendor/bin/pint
```

- [ ] **Step 4: Verify lint passes**

```bash
cd src/backend && php vendor/bin/pint --test
```
Expected: `PASS`

- [ ] **Step 5: Commit**

```bash
git add src/backend/pint.json src/backend/composer.json
git commit -m "feat(quality): configure Laravel Pint with PSR-12 rules"
```

---

### Task 3: Update CI Pipeline

**Files:**
- Modify: `.github/workflows/ci.yml`

- [ ] **Step 1: Add lint and analyse steps to CI**

After `composer install` step in backend job, add:

```yaml
      - name: Check code style
        working-directory: src/backend
        run: php vendor/bin/pint --test

      - name: Static analysis
        working-directory: src/backend
        run: php vendor/bin/phpstan analyse --no-progress --error-format=github
```

- [ ] **Step 2: Commit**

```bash
git add .github/workflows/ci.yml
git commit -m "ci: add pint check and phpstan analysis to backend job"
```

---

### Task 4: Create AppException Base Class

**Files:**
- Create: `src/backend/app/Exceptions/AppException.php`

- [ ] **Step 1: Create AppException abstract class**

```php
<?php

namespace App\Exceptions;

abstract class AppException extends \RuntimeException
{
    abstract public function getStatusCode(): int;

    abstract public function getErrorCode(): string;

    final public function render(): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'code' => $this->getErrorCode(),
            'errors' => (object) [],
        ], $this->getStatusCode());
    }
}
```

- [ ] **Step 2: Create example domain exceptions**

```php
// src/backend/app/Modules/Reporting/Domain/Exceptions/ReportDefinitionNotFoundException.php
<?php

namespace App\Modules\Reporting\Domain\Exceptions;

use App\Exceptions\AppException;

class ReportDefinitionNotFoundException extends AppException
{
    public function __construct(string $code)
    {
        parent::__construct("Không tìm thấy báo cáo với mã: {$code}");
    }

    public function getStatusCode(): int { return 404; }
    public function getErrorCode(): string { return 'REPORT_DEFINITION_NOT_FOUND'; }
}
```

Similarly for `ReportRunNotFoundException`.

Actually, let me list all domain exceptions that need migration:

- `src/backend/app/Modules/Reporting/Domain/Exceptions/ReportDefinitionNotFoundException.php`
- `src/backend/app/Modules/Reporting/Domain/Exceptions/ReportRunNotFoundException.php`
- (Find all existing domain exceptions)

- [ ] **Step 3: Find all existing domain exceptions**

```bash
find src/backend/app/Modules -name "*Exception.php" | sort
```

- [ ] **Step 4: Migrate each to extend AppException**

For each exception:
1. Change `extends \Exception` (or similar) to `extends AppException`
2. Add `getStatusCode()` and `getErrorCode()` methods
3. Update message to Vietnamese, user-friendly

- [ ] **Step 5: Run tests to verify no breakage**

```bash
cd src/backend && php artisan test --compact
```

- [ ] **Step 6: Commit**

```bash
git add src/backend/app/Exceptions/AppException.php [all modified exception files]
git commit -m "feat(quality): add AppException base class, migrate domain exceptions"
```

---

### Task 5: Custom Exception Handler

**Files:**
- Modify: `src/backend/app/Exceptions/Handler.php`
- Create: `src/backend/tests/Feature/Shared/ExceptionHandlerTest.php` (optional)

- [ ] **Step 1: Read current Handler.php**

```bash
cat src/backend/app/Exceptions/Handler.php
```

- [ ] **Step 2: Implement render() method**

```php
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Illuminate\Database\QueryException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $levels = [];

    protected $dontReport = [];

    public function render($request, Throwable $e)
    {
        // Let Laravel handle non-API requests
        if (!$request->expectsJson()) {
            return parent::render($request, $e);
        }

        // AppException — already has render()
        if ($e instanceof AppException) {
            return $e->render();
        }

        // ValidationException — 422
        if ($e instanceof ValidationException) {
            return response()->json([
                'message' => 'Dữ liệu không hợp lệ',
                'code' => 'VALIDATION_ERROR',
                'errors' => $e->errors(),
            ], 422);
        }

        // AuthenticationException — 401
        if ($e instanceof AuthenticationException) {
            return response()->json([
                'message' => 'Bạn cần đăng nhập để tiếp tục',
                'code' => 'UNAUTHENTICATED',
                'errors' => (object) [],
            ], 401);
        }

        // AuthorizationException — 403
        if ($e instanceof AuthorizationException) {
            return response()->json([
                'message' => 'Bạn không có quyền thực hiện hành động này',
                'code' => 'FORBIDDEN',
                'errors' => (object) [],
            ], 403);
        }

        // ModelNotFoundException — 404
        if ($e instanceof ModelNotFoundException) {
            $modelName = class_basename($e->getModel());
            $message = "Không tìm thấy {$modelName}";
            return response()->json([
                'message' => $message,
                'code' => 'MODEL_NOT_FOUND',
                'errors' => (object) [],
            ], 404);
        }

        // NotFoundHttpException — 404
        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'message' => 'Đường dẫn không tồn tại',
                'code' => 'NOT_FOUND',
                'errors' => (object) [],
            ], 404);
        }

        // ThrottleRequestsException — 429
        if ($e instanceof TooManyRequestsHttpException) {
            return response()->json([
                'message' => 'Bạn đã gửi quá nhiều yêu cầu, vui lòng thử lại sau',
                'code' => 'TOO_MANY_REQUESTS',
                'errors' => (object) [],
            ], 429);
        }

        // HttpExceptionInterface (other 4xx/5xx)
        if ($e instanceof HttpExceptionInterface) {
            return response()->json([
                'message' => $e->getMessage() ?: 'Lỗi yêu cầu',
                'code' => 'HTTP_ERROR',
                'errors' => (object) [],
            ], $e->getStatusCode());
        }

        // QueryException — 500, log stack
        if ($e instanceof QueryException) {
            logger()->error($e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'Có lỗi hệ thống xảy ra, vui lòng thử lại sau',
                'code' => 'DATABASE_ERROR',
                'errors' => (object) [],
            ], 500);
        }

        // Fallback — 500, log stack
        logger()->error($e->getMessage(), ['exception' => $e]);
        return response()->json([
            'message' => 'Có lỗi hệ thống xảy ra',
            'code' => 'INTERNAL_ERROR',
            'errors' => (object) [],
        ], 500);
    }
}
```

- [ ] **Step 3: Run tests**

```bash
cd src/backend && php artisan test --compact
```

Expected: All passing.

- [ ] **Step 4: Commit**

```bash
git add src/backend/app/Exceptions/Handler.php
git commit -m "feat(quality): custom JSON exception handler with Vietnamese messages"
```

---

### Task 6: Create BaseFormRequest

**Files:**
- Create: `src/backend/app/Http/Requests/BaseFormRequest.php`

- [ ] **Step 1: Create BaseFormRequest**

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class BaseFormRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->sanitize();
    }

    protected function sanitize(): void
    {
        $input = $this->all();
        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                $value = trim($value);
            }
        });
        $this->replace($input);
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add src/backend/app/Http/Requests/BaseFormRequest.php
git commit -m "feat(quality): add BaseFormRequest with input sanitization"
```

---

### Task 7: Add Form Requests — Module 1 (Attendance)

**Files:**
- Create: `src/backend/app/Modules/Attendance/Infrastructure/Http/Requests/StoreAttendanceAdjustmentRequest.php`
- Create: `src/backend/app/Modules/Attendance/Infrastructure/Http/Requests/StoreAttendanceRawLogRequest.php`

- [ ] **Step 1: Create StoreAttendanceAdjustmentRequest**

```php
<?php

namespace App\Modules\Attendance\Infrastructure\Http\Requests;

use App\Http\Requests\BaseFormRequest;

class StoreAttendanceAdjustmentRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true; // permission check in middleware
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|uuid|exists:employees,id',
            'date' => 'required|date_format:Y-m-d',
            'type' => 'required|string|in:clock_in,clock_out,late,early_leave,overtime',
            'reason' => 'required|string|max:500',
            'original_time' => 'sometimes|date_format:H:i:s',
            'adjusted_time' => 'required|date_format:H:i:s',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Vui lòng chọn nhân viên',
            'employee_id.exists' => 'Nhân viên không tồn tại',
            'date.required' => 'Vui lòng chọn ngày',
            'date.date_format' => 'Ngày không đúng định dạng (YYYY-MM-DD)',
            'type.required' => 'Vui lòng chọn loại điều chỉnh',
            'type.in' => 'Loại điều chỉnh không hợp lệ',
            'reason.required' => 'Vui lòng nhập lý do',
            'reason.max' => 'Lý do không được vượt quá 500 ký tự',
            'adjusted_time.required' => 'Vui lòng nhập giờ điều chỉnh',
            'adjusted_time.date_format' => 'Giờ không đúng định dạng (HH:MM:SS)',
        ];
    }
}
```

- [ ] **Step 2: Create StoreAttendanceRawLogRequest**

```php
<?php

namespace App\Modules\Attendance\Infrastructure\Http\Requests;

use App\Http\Requests\BaseFormRequest;

class StoreAttendanceRawLogRequest extends BaseFormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|uuid|exists:employees,id',
            'device_id' => 'required|string|max:100',
            'timestamp' => 'required|date_format:Y-m-d H:i:s',
            'mode' => 'sometimes|in:in,out',
        ];
    }

    public function messages(): array
    {
        return [
            'employee_id.required' => 'Vui lòng chọn nhân viên',
            'employee_id.exists' => 'Nhân viên không tồn tại',
            'device_id.required' => 'Vui lòng nhập mã thiết bị',
            'timestamp.required' => 'Vui lòng nhập thời gian',
            'timestamp.date_format' => 'Thời gian không đúng định dạng (YYYY-MM-DD HH:MM:SS)',
            'mode.in' => 'Chế độ không hợp lệ (in/out)',
        ];
    }
}
```

- [ ] **Step 3: Run tests**

```bash
cd src/backend && php artisan test --compact
```

- [ ] **Step 4: Commit**

```bash
git add src/backend/app/Modules/Attendance/Infrastructure/Http/Requests/
git commit -m "feat(quality): add Attendance Form Requests with Vietnamese validation messages"
```

---

### Task 8: Add Form Requests — Module 2 (Notification)

**Files:**
- Create: `src/backend/app/Modules/Notification/Infrastructure/Http/Requests/StoreMessageTemplateRequest.php`
- Create: `src/backend/app/Modules/Notification/Infrastructure/Http/Requests/SendNotificationRequest.php`

- [ ] **Step 1: Create StoreMessageTemplateRequest**

```php
<?php

namespace App\Modules\Notification\Infrastructure\Http\Requests;

use App\Http\Requests\BaseFormRequest;

class StoreMessageTemplateRequest extends BaseFormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:message_templates,code',
            'name' => 'required|string|max:200',
            'type' => 'required|string|max:50',
            'channels' => 'required|array',
            'channels.*' => 'in:email,sms,in_app',
            'subject' => 'sometimes|string|max:200',
            'body' => 'required|string',
        ];
    }
    public function messages(): array
    {
        return [
            'code.required' => 'Vui lòng nhập mã mẫu tin nhắn',
            'code.unique' => 'Mã mẫu tin nhắn đã tồn tại',
            'name.required' => 'Vui lòng nhập tên mẫu tin nhắn',
            'type.required' => 'Vui lòng chọn loại',
            'channels.required' => 'Vui lòng chọn kênh gửi',
            'channels.*.in' => 'Kênh gửi không hợp lệ (email/sms/in_app)',
            'body.required' => 'Vui lòng nhập nội dung',
        ];
    }
}
```

- [ ] **Step 2: Create SendNotificationRequest**

```php
<?php

namespace App\Modules\Notification\Infrastructure\Http\Requests;

use App\Http\Requests\BaseFormRequest;

class SendNotificationRequest extends BaseFormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|uuid|exists:users,id',
            'type' => 'required|string|max:50',
            'title' => 'required|string|max:200',
            'body' => 'required|string',
            'channels' => 'sometimes|array',
            'channels.*' => 'in:email,sms,in_app',
        ];
    }
    public function messages(): array
    {
        return [
            'user_ids.required' => 'Vui lòng chọn người nhận',
            'user_ids.*.exists' => 'Người nhận không tồn tại',
            'type.required' => 'Vui lòng chọn loại thông báo',
            'title.required' => 'Vui lòng nhập tiêu đề',
            'body.required' => 'Vui lòng nhập nội dung',
        ];
    }
}
```

- [ ] **Step 3: Run tests**

```bash
cd src/backend && php artisan test --compact
```

- [ ] **Step 4: Commit**

```bash
git add src/backend/app/Modules/Notification/Infrastructure/Http/Requests/
git commit -m "feat(quality): add Notification Form Requests"
```

---

### Task 9: Add Form Requests — Module 3 (Shift + Asset)

**Files:**
- Create: `src/backend/app/Modules/Shift/Infrastructure/Http/Requests/StoreShiftTemplateRequest.php`
- Create: `src/backend/app/Modules/Shift/Infrastructure/Http/Requests/StoreShiftAssignmentRequest.php`
- Create: `src/backend/app/Modules/Asset/Infrastructure/Http/Requests/StoreAssetRequest.php`
- Create: `src/backend/app/Modules/Asset/Infrastructure/Http/Requests/AssignAssetRequest.php`

- [ ] **Step 1: Create StoreShiftTemplateRequest**

```php
<?php

namespace App\Modules\Shift\Infrastructure\Http\Requests;

use App\Http\Requests\BaseFormRequest;

class StoreShiftTemplateRequest extends BaseFormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'code' => 'required|string|max:20|unique:shift_templates,code',
            'name' => 'required|string|max:200',
            'shift_type' => 'required|in:fixed,flexible,rotating',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'grace_minutes' => 'sometimes|integer|min:0|max:120',
            'description' => 'sometimes|string|max:500',
        ];
    }
    public function messages(): array
    {
        return [
            'code.required' => 'Vui lòng nhập mã ca',
            'code.unique' => 'Mã ca đã tồn tại',
            'name.required' => 'Vui lòng nhập tên ca',
            'shift_type.required' => 'Vui lòng chọn loại ca',
            'shift_type.in' => 'Loại ca không hợp lệ (fixed/flexible/rotating)',
            'start_time.required' => 'Vui lòng nhập giờ bắt đầu',
            'end_time.required' => 'Vui lòng nhập giờ kết thúc',
            'end_time.after' => 'Giờ kết thúc phải sau giờ bắt đầu',
        ];
    }
}
```

- [ ] **Step 2: Create StoreShiftAssignmentRequest**

```php
<?php

namespace App\Modules\Shift\Infrastructure\Http\Requests;

use App\Http\Requests\BaseFormRequest;

class StoreShiftAssignmentRequest extends BaseFormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'employee_id' => 'required|uuid|exists:employees,id',
            'shift_template_id' => 'required|uuid|exists:shift_templates,id',
            'effective_date' => 'required|date_format:Y-m-d',
            'end_date' => 'sometimes|date_format:Y-m-d|after_or_equal:effective_date',
        ];
    }
    public function messages(): array
    {
        return [
            'employee_id.required' => 'Vui lòng chọn nhân viên',
            'employee_id.exists' => 'Nhân viên không tồn tại',
            'shift_template_id.required' => 'Vui lòng chọn ca làm việc',
            'shift_template_id.exists' => 'Ca làm việc không tồn tại',
            'effective_date.required' => 'Vui lòng chọn ngày hiệu lực',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày hiệu lực',
        ];
    }
}
```

- [ ] **Step 3: Create StoreAssetRequest**

```php
<?php

namespace App\Modules\Asset\Infrastructure\Http\Requests;

use App\Http\Requests\BaseFormRequest;

class StoreAssetRequest extends BaseFormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:assets,code',
            'name' => 'required|string|max:200',
            'type' => 'required|string|max:50',
            'status' => 'sometimes|in:available,assigned,maintenance,retired',
            'description' => 'sometimes|string|max:1000',
        ];
    }
    public function messages(): array
    {
        return [
            'code.required' => 'Vui lòng nhập mã tài sản',
            'code.unique' => 'Mã tài sản đã tồn tại',
            'name.required' => 'Vui lòng nhập tên tài sản',
            'type.required' => 'Vui lòng chọn loại tài sản',
            'status.in' => 'Trạng thái không hợp lệ',
        ];
    }
}
```

- [ ] **Step 4: Create AssignAssetRequest**

```php
<?php

namespace App\Modules\Asset\Infrastructure\Http\Requests;

use App\Http\Requests\BaseFormRequest;

class AssignAssetRequest extends BaseFormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'asset_id' => 'required|uuid|exists:assets,id',
            'employee_id' => 'required|uuid|exists:employees,id',
            'assigned_at' => 'sometimes|date_format:Y-m-d',
            'note' => 'sometimes|string|max:500',
        ];
    }
    public function messages(): array
    {
        return [
            'asset_id.required' => 'Vui lòng chọn tài sản',
            'asset_id.exists' => 'Tài sản không tồn tại',
            'employee_id.required' => 'Vui lòng chọn nhân viên',
            'employee_id.exists' => 'Nhân viên không tồn tại',
        ];
    }
}
```

- [ ] **Step 5: Run tests**

```bash
cd src/backend && php artisan test --compact
```

- [ ] **Step 6: Commit**

```bash
git add src/backend/app/Modules/Shift/Infrastructure/Http/Requests/ src/backend/app/Modules/Asset/Infrastructure/Http/Requests/
git commit -m "feat(quality): add Shift and Asset Form Requests"
```

---

### Task 10: Add Form Requests — Remaining Modules

**Files:**
- Create: `src/backend/app/Modules/Onboarding/Infrastructure/Http/Requests/StoreOnboardingPlanRequest.php`
- Create: `src/backend/app/Modules/Onboarding/Infrastructure/Http/Requests/StoreOnboardingTemplateRequest.php`
- Create: `src/backend/app/Modules/Performance/Infrastructure/Http/Requests/StorePerformanceReviewRequest.php`
- Create: `src/backend/app/Modules/Performance/Infrastructure/Http/Requests/StorePerformanceGoalRequest.php`
- Create: `src/backend/app/Modules/Recruitment/Infrastructure/Http/Requests/StoreRequisitionRequest.php`
- Create: `src/backend/app/Modules/Recruitment/Infrastructure/Http/Requests/UpdateCandidateStatusRequest.php`
- Create: `src/backend/app/Modules/Reporting/Infrastructure/Http/Requests/StoreReportDefinitionRequest.php`
- Create: `src/backend/app/Modules/Training/Infrastructure/Http/Requests/StoreTrainingCourseRequest.php`
- Create: `src/backend/app/Modules/Training/Infrastructure/Http/Requests/StoreTrainingSessionRequest.php`

- [ ] **Step 1: Create each Form Request following the same BaseFormRequest pattern with Vietnamese messages**

Details for each are similar to Tasks 7-9 patterns. Key rules per module:

| Request | Key rules |
|---------|-----------|
| `StoreOnboardingPlanRequest` | `employee_id` required, `template_id` required, `start_date` required |
| `StoreOnboardingTemplateRequest` | `code` required unique, `name` required, `department` required |
| `StorePerformanceReviewRequest` | `employee_id` required, `review_period` required, `type` in enum |
| `StorePerformanceGoalRequest` | `employee_id` required, `title` required, `due_date` required after today |
| `StoreRequisitionRequest` | `title` required, `department` required, `position_count` integer min:1 |
| `UpdateCandidateStatusRequest` | `status` required in:new/reviewing/interviewed/offered/hired/rejected |
| `StoreReportDefinitionRequest` | `code` required unique, `name` required, `type` required |
| `StoreTrainingCourseRequest` | `code` required unique, `title` required, `duration_hours` numeric |
| `StoreTrainingSessionRequest` | `course_id` required exists, `trainer` required, `start_date` required after now |

- [ ] **Step 2: Run tests**

```bash
cd src/backend && php artisan test --compact
```

- [ ] **Step 3: Commit**

```bash
git add src/backend/app/Modules/Onboarding/Infrastructure/Http/Requests/ src/backend/app/Modules/Performance/Infrastructure/Http/Requests/ src/backend/app/Modules/Recruitment/Infrastructure/Http/Requests/ src/backend/app/Modules/Reporting/Infrastructure/Http/Requests/ src/backend/app/Modules/Training/Infrastructure/Http/Requests/
git commit -m "feat(quality): add Form Requests for Onboarding, Performance, Recruitment, Reporting, Training"
```

---

### Task 11: Full Suite Verification

- [ ] **Step 1: Run complete test suite**

```bash
cd src/backend && php artisan test --compact
```

Expected: All 176+ tests pass.

- [ ] **Step 2: Verify no PHPStan regression**

```bash
cd src/backend && php vendor/bin/phpstan analyse --level=5 --memory-limit=2G
```

Expected: `[OK] No errors`

- [ ] **Step 3: Verify Pint passes**

```bash
cd src/backend && php vendor/bin/pint --test
```

Expected: `PASS`

- [ ] **Step 4: Final commit if any fixes needed**
