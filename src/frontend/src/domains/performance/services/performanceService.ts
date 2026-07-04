import { http } from '@/core/http/client';
import type { CreateCyclePayload, CreateGoalPayload, Goal, PerformanceCycle, Review } from '@/domains/performance/models/performance';
interface ApiListResponse<T> { data: T[] } interface ApiOneResponse<T> { data: T }

export const performanceService = {
  // Reviews
  async getReviews(): Promise<Review[]> { const r=await http.get<ApiListResponse<Review>>('/performance/reviews'); return r.data.data; },
  async submitReview(id:string, role:'self'|'manager'|'hr', assessment:Record<string,unknown>): Promise<void> { await http.post(`/performance/reviews/${id}/${role}`, {assessment}); },
  async finalizeReview(id:string, finalScore:number): Promise<void> { await http.post(`/performance/reviews/${id}/finalize`, {final_score:finalScore}); },
  // Cycles
  async getCycles(): Promise<PerformanceCycle[]> { const r=await http.get<ApiListResponse<PerformanceCycle>>('/performance/cycles'); return r.data.data; },
  async createCycle(p: CreateCyclePayload): Promise<string> { const r=await http.post<{id:string}>('/performance/cycles',p); return r.data.id; },
  async cycleAction(id:string,action:'activate'|'complete'|'cancel'): Promise<void> { await http.post(`/performance/cycles/${id}/${action}`); },
  // Goals
  async getGoals(): Promise<Goal[]> { const r=await http.get<ApiListResponse<Goal>>('/performance/goals'); return r.data.data; },
  async createGoal(p: CreateGoalPayload): Promise<Goal> { const r=await http.post<ApiOneResponse<Goal>>('/performance/goals',p); return r.data.data; },
  async completeGoal(id:string): Promise<void> { await http.post(`/performance/goals/${id}/complete`); },
};
