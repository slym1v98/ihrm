export interface Department {
  id: string;
  branch_id: string;
  parent_id: string | null;
  code: string;
  name: string;
  manager_employee_id: string | null;
  status: string;
  parent?: Department | null;
  created_at: string;
  updated_at: string;
}

export interface CreateDepartmentPayload {
  code: string;
  name: string;
  branch_id: string;
  parent_id?: string;
}

export interface UpdateDepartmentPayload {
  name: string;
  manager_employee_id?: string | null;
}

export interface MoveDepartmentPayload {
  new_parent_id: string | null;
}
