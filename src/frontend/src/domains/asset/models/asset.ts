export interface AssetItem {
  id: string;
  asset_code: string;
  asset_type: string;
  name: string;
  serial_number: string | null;
  condition: string;
  status: string;
  notes: string | null;
}

export interface AssetAssignment {
  id: string;
  asset_item_id: string;
  employee_id: string;
  issued_at: string;
  expected_return_at: string | null;
  condition_on_issue: string;
  status: string;
}

export interface CreateAssetItemPayload {
  asset_code: string;
  asset_type: string;
  name: string;
  serial_number?: string;
  notes?: string;
}

export interface AssignAssetPayload {
  asset_item_id: string;
  employee_id: string;
  expected_return_at?: string;
  condition_on_issue?: string;
}
