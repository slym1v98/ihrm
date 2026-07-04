import { http } from '@/core/http/client';
import type { Candidate, CreateCandidatePayload, CreateRequisitionPayload, Requisition } from '@/domains/recruitment/models/recruitment';
interface ApiListResponse<T> { data: T[] }

export const recruitmentService = {
  async getOffers(): Promise<{id:string;candidate_id:string;position_id:string;status:string;offered_at:string|null}[]> { const r=await http.get<ApiListResponse<{id:string;candidate_id:string;position_id:string;status:string;offered_at:string|null}>>('/recruitment/offers'); return r.data.data; },
  async getInterviews(): Promise<{id:string;candidate_id:string;interview_date:string;status:string}[]> { const r=await http.get<ApiListResponse<{id:string;candidate_id:string;interview_date:string;status:string}>>('/recruitment/interviews'); return r.data.data; },
  async getRequisitions(): Promise<Requisition[]> { const r=await http.get<ApiListResponse<Requisition>>('/recruitment/requisitions'); return r.data.data; },
  async createRequisition(p: CreateRequisitionPayload): Promise<Requisition> { const r=await http.post<{data:Requisition}>('/recruitment/requisitions',p); return r.data.data; },
  async getCandidates(): Promise<Candidate[]> { const r=await http.get<ApiListResponse<Candidate>>('/recruitment/candidates'); return r.data.data; },
  async createCandidate(p: CreateCandidatePayload): Promise<Candidate> { const r=await http.post<{data:Candidate}>('/recruitment/candidates',p); return r.data.data; },
  async updateCandidateStage(id:string,stage:string): Promise<void> { await http.patch(`/recruitment/candidates/${id}/stage`,{stage}); },
};
