export interface ReportDefinition { id: string; code: string; name: string; description: string | null; filters_schema: unknown; columns_schema: unknown; is_active: boolean }
export interface ReportRun { id: string; report_definition_id: string; requested_by: string; filters: unknown; status: string; result: unknown; error: string | null; started_at: string | null; completed_at: string | null }
