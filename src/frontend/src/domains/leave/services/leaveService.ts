import { http } from '@/core/http/client';
import type { LeaveType, LeavePolicy, LeaveRequest, LeaveBalance, CreateLeaveRequestPayload } from '@/domains/leave/models/leave';

interface ApiListResponse<T> {
  data: T[];
}

export const leaveService = {
  async getTypes(): Promise<LeaveType[]> {
    const res = await http.get<ApiListResponse<LeaveType>>('/leave-types');
    return res.data.data;
  },

  async getPolicies(): Promise<LeavePolicy[]> {
    const res = await http.get<ApiListResponse<LeavePolicy>>('/leave-policies');
    return res.data.data;
  },

  async getRequests(): Promise<LeaveRequest[]> {
    const res = await http.get<ApiListResponse<LeaveRequest>>('/leave-requests');
    return res.data.data;
  },

  async getRequest(id: string): Promise<LeaveRequest> {
    const res = await http.get<{ data: LeaveRequest }>(`/leave-requests/${id}`);
    return res.data.data;
  },

  async createRequest(payload: CreateLeaveRequestPayload): Promise<LeaveRequest> {
    const res = await http.post<{ data: LeaveRequest }>('/leave-requests', payload);
    return res.data.data;
  },

  async approveRequest(id: string): Promise<void> {
    await http.post(`/leave-requests/${id}/approve`);
  },

  async rejectRequest(id: string, reason: string): Promise<void> {
    await http.post(`/leave-requests/${id}/reject`, { reason });
  },

  async cancelRequest(id: string): Promise<void> {
    await http.post(`/leave-requests/${id}/cancel`);
  },

  async getBalances(): Promise<LeaveBalance[]> {
    const res = await http.get<ApiListResponse<LeaveBalance>>('/leave-balances');
    return res.data.data;
  },

  async getBalanceSummary(): Promise<LeaveBalance[]> {
    const res = await http.get<ApiListResponse<LeaveBalance>>('/leave-balances/summary');
    return res.data.data;
  },
};
