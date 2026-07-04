export interface OnboardingTemplate { id: string; code: string; name: string; active: boolean }
export interface OnboardingPlan { id: string; employee_id: string; status: string; start_date: string }
export interface CreateOnboardingTemplatePayload { code: string; name: string; rules: Record<string, unknown> }
export interface CreateOnboardingPlanPayload { employee_id: string; candidate_id?: string; template_id?: string; start_date: string }
