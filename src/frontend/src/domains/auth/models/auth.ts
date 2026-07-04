export interface RoleSummary {
  id: string;
  code: string;
  name: string;
}

export interface User {
  id: string;
  email: string;
  name: string;
  employee_id: string | null;
  status: string;
  last_login_at: string | null;
  roles: RoleSummary[];
}

export interface LoginResponse {
  access_token: string;
  token_type: string;
  user: User;
}

export interface RoleDetail extends RoleSummary {
  permissions: string[];
}

export interface ApiResponse<T> {
  data: T;
}
