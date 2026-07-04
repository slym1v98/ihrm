import { http } from '@/core/http/client';
import type { CreateOnboardingPlanPayload, CreateOnboardingTemplatePayload, OnboardingPlan, OnboardingTemplate } from '@/domains/onboarding/models/onboarding';

interface ApiListResponse<T> { data: T[] }
interface ApiOneResponse<T> { data: T }

export const onboardingService = {
  async getTemplates(): Promise<OnboardingTemplate[]> { const r = await http.get<ApiListResponse<OnboardingTemplate>>('/onboarding/templates'); return r.data.data; },
  async createTemplate(payload: CreateOnboardingTemplatePayload): Promise<OnboardingTemplate> { const r = await http.post<ApiOneResponse<OnboardingTemplate>>('/onboarding/templates', payload); return r.data.data; },
  async getPlans(): Promise<OnboardingPlan[]> { const r = await http.get<ApiListResponse<OnboardingPlan>>('/onboarding/plans'); return r.data.data; },
  async createPlan(payload: CreateOnboardingPlanPayload): Promise<OnboardingPlan> { const r = await http.post<ApiOneResponse<OnboardingPlan>>('/onboarding/plans', payload); return r.data.data; },
  async activatePlan(id: string): Promise<void> { await http.post(`/onboarding/plans/${id}/activate`); },
  async cancelPlan(id: string): Promise<void> { await http.post(`/onboarding/plans/${id}/cancel`); },
  async completePlan(id: string): Promise<void> { await http.post(`/onboarding/plans/${id}/complete`); },
};
