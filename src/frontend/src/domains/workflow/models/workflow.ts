export interface WorkflowTemplate { id: string; code: string; name: string; description: string | null; active: boolean }
export interface WorkflowRequest { id: string; workflow_template_id: string; subject_type: string; subject_id: string; status: string; submitted_by: string; created_at: string | null }
