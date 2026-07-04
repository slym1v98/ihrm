export interface SystemSetting { id: string; key: string; value: string; description: string | null; created_at: string | null }
export interface HolidayCalendar { id: string; code: string; name: string; year: number; description: string | null }
export interface LookupGroup { id: string; code: string; name: string; values: LookupValue[] }
export interface LookupValue { id: string; code: string; name: string; sort_order: number }
