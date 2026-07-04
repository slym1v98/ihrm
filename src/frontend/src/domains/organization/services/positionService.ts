import { http } from '@/core/http/client';
import type { Position, CreatePositionPayload, UpdatePositionPayload } from '@/domains/organization/models/position';

export const positionService = {
  async list(params?: Record<string, string>) {
    const res = await http.get<{ data: Position[] }>('/positions', { params });
    return res.data;
  },

  async get(id: string) {
    const res = await http.get<{ data: Position }>(`/positions/${id}`);
    return res.data.data;
  },

  async create(payload: CreatePositionPayload) {
    const res = await http.post<{ data: Position }>('/positions', payload);
    return res.data.data;
  },

  async update(id: string, payload: UpdatePositionPayload) {
    const res = await http.patch<{ data: Position }>(`/positions/${id}`, payload);
    return res.data.data;
  },

  async toggleStatus(id: string, action: 'activate' | 'deactivate') {
    const res = await http.post<{ data: Position }>(`/positions/${id}/${action}`);
    return res.data.data;
  },
};
