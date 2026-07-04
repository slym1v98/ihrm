export interface LeaveType {
  id: string;
  name: string;
  code: string;
  is_balance_tracked: boolean;
  is_active: boolean;
  sort_order: number;
}

export interface LeavePolicy {
  id: string;
  leave_type_id: string;
  valid_from: string;
  valid_until: string | null;
  max_consecutive_days: number;
  requires_attachment: boolean;
  carry_over_limit: number;
  carry_over_expiry_months: number;
  half_day_allowed: boolean;
  hourly_allowed: boolean;
}

export interface LeaveRequest {
  id: string;
  employee_id: string;
  leave_type_id: string;
  start_at: string;
  end_at: string;
  duration_unit: 'day' | 'half_day' | 'hour';
  duration_minutes: number;
  reason: string | null;
  status: 'pending' | 'approved' | 'rejected' | 'cancelled';
  approved_by: string | null;
  approved_at: string | null;
  rejected_reason: string | null;
  created_at: string | null;
  updated_at: string | null;
}

export interface LeaveBalance {
  id: string;
  employee_id: string;
  leave_type_id: string;
  year: number;
  opening: number;
  accrued: number;
  used: number;
  carried_over: number;
  expired: number;
  remaining: number;
}

export interface CreateLeaveRequestPayload {
  leave_type_id: string;
  start_at: string;
  end_at: string;
  duration_unit: 'day' | 'half_day' | 'hour';
  reason?: string;
}
