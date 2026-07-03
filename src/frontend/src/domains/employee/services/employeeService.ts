import { http } from '@/core/http/client';
import type { Employee, CreateEmployeePayload, UpdatePersonalInfoPayload } from '@/domains/employee/models/employee';

export const employeeService = {
  async list(params?: Record<string, string>) {
    const res = await http.get<{ data: Employee[]; meta: { current_page: number; per_page: number; total: number; last_page: number } }>('/employees', { params });
    return res.data;
  },
  async get(id: string) {
    const res = await http.get<{ data: Employee }>(`/employees/${id}`);
    return res.data.data;
  },
  async create(payload: CreateEmployeePayload) {
    const res = await http.post<{ data: Employee }>('/employees', payload);
    return res.data.data;
  },
  async updatePersonalInfo(id: string, payload: UpdatePersonalInfoPayload) {
    const res = await http.patch<{ data: Employee }>(`/employees/${id}/personal-info`, payload);
    return res.data.data;
  },
  async transfer(id: string, payload: { branch_id?: string; department_id?: string; position_id?: string }) {
    const res = await http.patch<{ data: Employee }>(`/employees/${id}/employment`, payload);
    return res.data.data;
  },
  async changeStatus(id: string, action: 'activate' | 'deactivate') {
    const res = await http.patch<{ data: Employee }>(`/employees/${id}/status`, { action });
    return res.data.data;
  },
};
