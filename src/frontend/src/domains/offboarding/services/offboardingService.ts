import { http } from '@/core/http/client';
import type { CreateOffboardingPayload, OffboardingRequest } from '@/domains/offboarding/models/offboarding';
interface ApiListResponse<T> { data: T[] } interface ApiOneResponse<T> { data: T }
export const offboardingService = {
  async getRequests(): Promise<OffboardingRequest[]> { const r=await http.get<ApiListResponse<OffboardingRequest>>('/offboarding/requests'); return r.data.data; },
  async createRequest(p: CreateOffboardingPayload): Promise<OffboardingRequest> { const r=await http.post<ApiOneResponse<OffboardingRequest>>('/offboarding/requests',p); return r.data.data; },
  async submitRequest(id: string): Promise<void> { await http.post(`/offboarding/requests/${id}/submit`); },
  async approveRequest(id: string): Promise<void> { await http.post(`/offboarding/requests/${id}/approve`); },
  async rejectRequest(id: string): Promise<void> { await http.post(`/offboarding/requests/${id}/reject`); },
};
