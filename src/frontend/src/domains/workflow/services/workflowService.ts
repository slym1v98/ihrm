import { http } from '@/core/http/client';
import type { WorkflowRequest, WorkflowTemplate } from '@/domains/workflow/models/workflow';
interface ApiListResponse<T> { data: T[] }

export const workflowService = {
  async getTemplates(): Promise<WorkflowTemplate[]> { const r=await http.get<ApiListResponse<WorkflowTemplate>>('/workflow-templates'); return r.data.data; },
  async getRequests(): Promise<WorkflowRequest[]> { const r=await http.get<ApiListResponse<WorkflowRequest>>('/workflow-requests'); return r.data.data; },
  async startRequest(payload:{workflow_template_id:string;subject_type:string;subject_id:string}): Promise<WorkflowRequest> { const r=await http.post<{data:WorkflowRequest}>('/workflow-requests',payload); return r.data.data; },
  async approveRequest(id:string): Promise<void> { await http.post(`/workflow-requests/${id}/approve`); },
  async rejectRequest(id:string): Promise<void> { await http.post(`/workflow-requests/${id}/reject`); },
  async cancelRequest(id:string): Promise<void> { await http.post(`/workflow-requests/${id}/cancel`); },
};
