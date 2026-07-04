export interface OffboardingRequest { id: string; employee_id: string; reason: string | null; status: string; created_at: string | null }
export interface CreateOffboardingPayload { employee_id: string; reason?: string }
