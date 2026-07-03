import { http } from '@/core/http/client';
import type { Branch, CreateBranchPayload, UpdateBranchPayload } from '@/domains/organization/models/branch';

export const branchService = {
  async list(params?: Record<string, string>) {
    const res = await http.get<{ data: Branch[] }>('/branches', { params });
    return res.data;
  },

  async get(id: string) {
    const res = await http.get<{ data: Branch }>(`/branches/${id}`);
    return res.data.data;
  },

  async create(payload: CreateBranchPayload) {
    const res = await http.post<{ data: Branch }>('/branches', payload);
    return res.data.data;
  },

  async update(id: string, payload: UpdateBranchPayload) {
    const res = await http.patch<{ data: Branch }>(`/branches/${id}`, payload);
    return res.data.data;
  },

  async toggleStatus(id: string, action: 'activate' | 'deactivate') {
    const res = await http.post<{ data: Branch }>(`/branches/${id}/${action}`);
    return res.data.data;
  },
};
