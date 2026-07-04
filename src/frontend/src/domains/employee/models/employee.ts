export interface Employee {
  id: string;
  employee_code: string;
  first_name: string;
  last_name: string;
  dob: string | null;
  gender: string | null;
  personal_email: string | null;
  phone: string | null;
  status: string;
  branch_id: string | null;
  department_id: string | null;
  position_id: string | null;
  manager_id: string | null;
  user_id: string | null;
  created_at: string;
  updated_at: string;
}

export interface CreateEmployeePayload {
  first_name: string;
  last_name: string;
}

export interface UpdatePersonalInfoPayload {
  first_name?: string;
  last_name?: string;
  dob?: string;
  gender?: string;
  personal_email?: string;
  phone?: string;
}
