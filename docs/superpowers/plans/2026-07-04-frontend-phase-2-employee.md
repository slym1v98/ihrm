# Employee + Contract Module Implementation Plan

**Goal:** Employee list, create/edit, detail with info/contracts/documents tabs.

**Structure:** Same pattern as Organization — DataTable + Dialog, React Query, react-hook-form.

---

### File Map

- Create: `src/frontend/src/domains/employee/models/employee.ts`
- Create: `src/frontend/src/domains/employee/models/contract.ts`
- Create: `src/frontend/src/domains/employee/services/employeeService.ts`
- Create: `src/frontend/src/domains/employee/services/contractService.ts`
- Create: `src/frontend/src/domains/employee/hooks/useEmployees.ts`
- Create: `src/frontend/src/domains/employee/hooks/useContracts.ts`
- Create: `src/frontend/src/domains/employee/components/EmployeeListPage.tsx`
- Create: `src/frontend/src/domains/employee/components/EmployeeDetailPage.tsx`
- Create: `src/frontend/src/domains/employee/components/ContractSection.tsx`
- Create: `src/frontend/src/app/(dashboard)/employees/page.tsx`
- Create: `src/frontend/src/app/(dashboard)/employees/[id]/page.tsx`
- Modify: `src/frontend/src/shared/components/AppSidebar.tsx`
- Add: `src/frontend/src/domains/employee/components/DocumentSection.tsx`

---

**Task 1: Models, Services, Hooks**
- models: `Employee` (id, employee_code, first_name, last_name, dob, gender, status, branch_id, dept_id, position_id, manager_id), `Contract` (id, contract_number, type, start_date, end_date, sign_date, status, base_salary, position_id)
- services: list (paginated), get, create, updatePersonalInfo, transfer, changeStatus | contracts: listByEmployee, create, activate, renew, terminate
- hooks: useEmployees(params), useEmployee(id), useCreateEmployee, useUpdateEmployee, useChangeStatus | useContracts(employeeId), useCreateContract, useActivateContract, useRenewContract, useTerminateContract

**Task 2: Employee List Page**
- `/employees` route
- DataTable columns: #, code, name, branch/department/position, status, actions
- Create dialog: first_name + last_name (required)
- Edit dialog opens detail page
- DataTable reuses existing component

**Task 3: Employee Detail Page**
- `/employees/[id]` route
- Tabs: Thông tin | Hợp đồng | Tài liệu
- Info tab: form edit personal info, employment info, manager
- Status change: activate/deactivate button

**Task 4: Contract Section**
- Embedded in Employee Detail's "Hợp đồng" tab
- Table: số HĐ, loại, start, end, status (badge), sắp hết hạn (warning badge nếu < 30 ngày)
- Actions: Kích hoạt, Gia hạn, Chấm dứt (xác nhận dialog)
- Create contract dialog: contract_number, type, start_date, end_date, sign_date, base_salary

**Task 5: Document Section**
- Document list: name, type, uploaded_at
- Download button (opens API download URL)

**Task 6: Sidebar + Build**
- Add "Nhân sự" nav section with Employees link
- Test: lint + build pass
