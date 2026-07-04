export interface PerformanceCycle {
  id: string; code: string; name: string; description: string | null;
  start_date: string; end_date: string; status: string; scoring_rules: unknown; workflow_request_id: string | null;
}
export interface Goal {
  id: string; cycle_id: string; employee_id: string; title: string;
  description: string | null; weight: number; target_value: number | null; actual_value: number | null; status: string; sort_order: number;
}
export interface CreateCyclePayload { code: string; name: string; description?: string; start_date: string; end_date: string; scoring_rules?: Record<string, unknown> }
export interface Review { id: string; cycle_id: string; employee_id: string; self_assessment: unknown; manager_assessment: unknown; hr_assessment: unknown; final_score: number | null; status: string; finalized_at: string | null }
export interface CreateGoalPayload { cycle_id: string; employee_id: string; title: string; description?: string; weight?: number; target_value?: number }
