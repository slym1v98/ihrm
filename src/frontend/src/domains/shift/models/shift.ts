export interface ShiftTemplate {
  id: string;
  code: string;
  name: string;
  start_time: string;
  end_time: string;
  is_overnight: boolean;
  break_minutes: number;
  late_tolerance_minutes: number;
  overtime_rules: Record<string, unknown> | null;
  flexibility_rules: Record<string, unknown> | null;
  payroll_attribution_rule: string | null;
  active: boolean;
  created_at: string | null;
  updated_at: string | null;
}

export interface ShiftAssignment {
  id: string;
  shift_template_id: string;
  assignable_type: string;
  assignable_id: string;
  effective_from: string;
  effective_to: string | null;
  recurrence_rule: string | null;
  active: boolean;
  created_at: string | null;
  updated_at: string | null;
}

export interface CreateShiftTemplatePayload {
  code: string;
  name: string;
  start_time: string;
  end_time: string;
  is_overnight?: boolean;
  break_minutes?: number;
  late_tolerance_minutes?: number;
}


export interface ShiftAssignment {
  id: string;
  shift_template_id: string;
  assignable_type: string;
  assignable_id: string;
  effective_from: string;
  effective_to: string | null;
  recurrence_rule: string | null;
  active: boolean;
  created_at: string | null;
  updated_at: string | null;
}
