# Quality & Reliability Improvement Plan

**Date:** 2026-07-05
**Context:** Phase 2 post-merge, backend quality gaps identified across 19 modules

---

## 1. Static Analysis & Code Style

### 1.1 PHPStan / Larastan

- Add `larastan/larastan` to `require-dev`
- Config tại `src/backend/phpstan.neon`:
  - Level 5 (balance strictness vs adoption cost)
  - Paths: `app/Modules` (scan từng module)
  - Exclude: `database/migrations`, `config`
  - Report unmatched ignored errors
- Script `composer.json`:
  - `"analyse": "phpstan analyse --memory-limit=2G"`
  - `"analyse:ci": "phpstan analyse --no-progress --error-format=github"`

### 1.2 Laravel Pint (đã có sẵn)

- Config tại `src/backend/pint.json`:
  - `preset: laravel`
  - `rules`: PSR-12, no unused imports, ordered imports
- Script `composer.json`:
  - `"lint": "pint"`
  - `"lint:test": "pint --test"`

### 1.3 CI Integration

Thêm vào `.github/workflows/ci.yml` backend job, sau `composer install`:

```yaml
      - name: Check code style
        working-directory: src/backend
        run: php vendor/bin/pint --test

      - name: Static analysis
        working-directory: src/backend
        run: php vendor/bin/phpstan analyse --no-progress --error-format=github
```

---

## 2. Exception Handling

### 2.1 App\Exceptions\AppException Base Class

```php
abstract class AppException extends \Exception
{
    abstract public function getStatusCode(): int;
    abstract public function getErrorCode(): string;  // machine-readable: 'EMPLOYEE_NOT_FOUND'
}
```

### 2.2 Custom Handler::render()

Tại `App\Exceptions\Handler::render()`:

| Exception | HTTP | message mẫu |
|-----------|------|-------------|
| `ValidationException` | 422 | Trả errors field → field messages (mặc định Laravel) |
| `AuthenticationException` | 401 | "Bạn cần đăng nhập để tiếp tục" |
| `AuthorizationException` | 403 | "Bạn không có quyền thực hiện hành động này" |
| `ModelNotFoundException` | 404 | "Không tìm thấy {model}" (VD: "Không tìm thấy nhân viên") |
| `NotFoundHttpException` | 404 | "Đường dẫn không tồn tại" |
| `ThrottleRequestsException` | 429 | "Bạn đã gửi quá nhiều yêu cầu, vui lòng thử lại sau" |
| `QueryException` | 500 | Log stack, response "Có lỗi hệ thống xảy ra, vui lòng thử lại sau" |
| `AppException` | theo class | message từ exception |
| `Throwable` (fallback) | 500 | Log stack, response "Có lỗi hệ thống xảy ra" |

Response format đồng nhất:

```json
{
    "message": "Không tìm thấy nhân viên",
    "code": "MODEL_NOT_FOUND",
    "errors": {}
}
```

`errors` chỉ xuất hiện với `ValidationException` (field → messages array).

### 2.3 Domain Exception Migration

Các domain exception hiện có (ví dụ `ReportDefinitionNotFoundException`, `ReportRunNotFoundException`) sẽ kế thừa `AppException` thay vì `\Exception`.

---

## 3. Form Request Validation

### 3.1 Đợt 1 — POST/PUT/PATCH

Các module chưa có Form Request cho CRUD chính:

| Module | Cần thêm |
|--------|----------|
| Attendance | `StoreAttendanceAdjustmentRequest`, `StoreAttendanceRawLogRequest` |
| Notification | `StoreMessageTemplateRequest`, `SendNotificationRequest` |
| Onboarding | `StoreOnboardingPlanRequest`, `StoreOnboardingTemplateRequest` |
| Performance | `StorePerformanceReviewRequest`, `StorePerformanceGoalRequest` |
| Recruitment | `StoreRequisitionRequest`, `UpdateCandidateStatusRequest` |
| Reporting | `StoreReportDefinitionRequest` |
| Shift | `StoreShiftTemplateRequest`, `StoreShiftAssignmentRequest` |
| Training | `StoreTrainingCourseRequest`, `StoreTrainingSessionRequest` |
| Asset | `StoreAssetRequest`, `AssignAssetRequest` |

Mỗi Form Request:
- `authorize()`: kiểm tra permission
- `rules()`: rule rõ ràng, dùng `exists`, `unique`, `in` enum validation
- `messages()`: tiếng Việt, người dùng hiểu được
  - VD: `'email.required' => 'Vui lòng nhập email'`
  - VD: `'email.email' => 'Email không đúng định dạng'`
  - VD: `'status.in' => 'Trạng thái không hợp lệ'`

### 3.2 Đợt 2 — GET list (ít ưu tiên)

Thêm validation cho params: `per_page`, `page`, `sort`, `filter[...]` với rule hợp lý.

### 3.3 Base Form Request

```php
abstract class BaseFormRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        // trim strings, normalize
    }
}
```

---

## 4. Acceptance Criteria (AC)

- **AC1:** `composer lint:test` pass không lỗi style
- **AC2:** `composer analyse` level 5 pass, zero error
- **AC3:** CI chạy lint + analyse + test cho backend
- **AC4:** Mọi API lỗi trả JSON format đồng nhất với message tiếng Việt
- **AC5:** Các domain exception kế thừa `AppException` đúng HTTP status
- **AC6:** Mỗi module có Form Request cho POST/PUT chính, validation message tiếng Việt
- **AC7:** Full test suite pass (hiện tại 176 tests)
