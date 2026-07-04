export interface Requisition { id: string; code: string; title: string; department_id: string; position_id: string; headcount: number; status: string }
export interface Candidate { id: string; requisition_id: string; name: string; email: string; phone: string | null; stage: string; status: string }
export interface CreateRequisitionPayload { code: string; title: string; department_id: string; position_id: string; headcount: number }
export interface CreateCandidatePayload { requisition_id: string; name: string; email: string; phone?: string }
