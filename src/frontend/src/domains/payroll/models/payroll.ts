export interface PayrollPeriod { id: string; period_code: string; start_date: string; end_date: string; status: string; approved_by: string | null; approved_at: string | null; locked_at: string | null }
export interface PayrollComponent { id: string; code: string; name: string; category: string; calculation_type: string; default_amount: number | null; default_percent: number | null; taxable: boolean; active: boolean }
export interface PayrollRun { id: string; payroll_period_id: string; status: string; started_by: string }
export interface Payslip { id: string; employee_id: string; period_id: string; gross: number; deductions: number; net: number; status: string; published_at: string | null; payload?: any }
export interface PayrollEntry { id: string; run_id: string; period_id: string; employee_id: string; gross_amount: number; deduction_amount: number; net_amount: number; status: string; error_message: string | null; reviewed_by: string | null; reviewed_at: string | null; contract_snapshot: any; lines: PayrollEntryLine[] }
export interface PayrollEntryLine { component_id: string; category: string; amount: number; calculation_note: string | null }
export interface PayrollAdjustment { id: string; entry_id: string; component_id: string; amount: number; reason: string; status: string; created_by: string | null; created_at: string | null }
export interface PeriodSummary { employee_count: number; total_gross: number; total_deductions: number; total_net: number; status: string; locked_at: string | null; period_code: string }
export interface CreatePeriodPayload { period_code: string; start_date: string; end_date: string; cutoff_date: string }
