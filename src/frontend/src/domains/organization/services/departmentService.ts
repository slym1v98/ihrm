import { http } from '@/core/http/client';
import type { Department, CreateDepartmentPayload, UpdateDepartmentPayload, MoveDepartmentPayload } from '@/domains/organization/models/department';

export const departmentService = {
  async list(params?: Record<string, string>) {
    const res = await http.get<{ data: Department[] }>('/departments', { params });
    return res.data;
  },

  async create(payload: CreateDepartmentPayload) {
    const res = await http.post<{ data: Department }>('/departments', payload);
    return res.data.data;
  },

  async update(id: string, payload: UpdateDepartmentPayload) {
    const res = await http.patch<{ data: Department }>(`/departments/${id}`, payload);
    return res.data.data;
  },

  async move(id: string, payload: MoveDepartmentPayload) {
    const res = await http.post<{ data: Department }>(`/departments/${id}/move`, payload);
    return res.data.data;
  },

  async toggleStatus(id: string, action: 'activate' | 'deactivate') {
    const res = await http.post<{ data: Department }>(`/departments/${id}/${action}`);
    return res.data.data;
  },
};
