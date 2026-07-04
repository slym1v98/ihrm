# Future Improvements (Backend)

Items deferred from frontend development that need backend changes.

## Contract Status: Distinguish Terminated vs Expired

Contract có `end_date` trong tương lai nhưng status `terminated` → nên hiển thị "Chấm dứt sớm" thay vì "Đã chấm dứt" chung chung.

**Gợi ý:** Frontend tính `isEarlyTerminated = status === 'terminated' && end_date > today`. Backend có thể thêm computed field `termination_type` trả về `early` | `natural`.

## Toggle Employee Status

API `PATCH /employees/{id}/status` yêu cầu `status` là `active` | `inactive`. Frontend hiện map `activate` → `active`, `deactivate` → `inactive`. Nên backend rename action endpoints thống nhất (giống Organization: activate/deactivate riêng biệt).

## Employee — All Fields In Create Request

`CreateEmployeeRequest` chỉ validate `first_name` + `last_name`. Các field khác phải update từng phần qua `PATCH /employees/{id}/personal-info`. Nên cho phép create với đầy đủ fields để đỡ round-trip.

## Move Department — Null Parent

`MoveDepartmentRequest` cho phép `new_parent_id: null` nhưng controller gọi `DepartmentId::fromString(null)` → crash. Đã fix bằng `$request->filled('new_parent_id')`. Giữ lại fix này khi merge backend.

## Organization Permissions

Permission middleware dùng code như `organization.branch.list`. Frontend cần map vào PermissionGuard. Đảm bảo error `FORBIDDEN` trả về `code: 'FORBIDDEN'` để frontend map đúng message.
