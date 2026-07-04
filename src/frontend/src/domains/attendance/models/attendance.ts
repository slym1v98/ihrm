export interface AttendancePeriod {
  id: string;
  period_code: string;
  start_date: string;
  end_date: string;
  status: string;
  created_at: string | null;
  updated_at: string | null;
}

export interface AttendanceTimesheet {
  id: string;
  attendance_period_id: string;
  employee_id: string;
  work_date: string;
  shift_assignment_id: string | null;
  expected_minutes: number;
  worked_minutes: number;
  late_minutes: number;
  early_leave_minutes: number;
  overtime_minutes: number;
  result_status: string;
  calculation_run_id: string | null;
  created_at: string | null;
  updated_at: string | null;
}

export interface AttendanceRawLog {
  id: string;
  employee_id: string;
  source: string;
  event_type: string;
  event_time: string;
  geo_point: unknown | null;
  payload: unknown | null;
  created_at: string | null;
}

export interface AttendanceAdjustmentRequest {
  id: string;
  attendance_timesheet_id: string;
  employee_id: string;
  requested_by: string;
  reason: string;
  evidence_file: string | null;
  corrections: unknown;
  status: string;
  approved_by: string | null;
  approved_at: string | null;
  created_at: string | null;
  updated_at: string | null;
}
