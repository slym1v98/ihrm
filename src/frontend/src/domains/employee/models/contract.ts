export interface Contract {
  id: string;
  employee_id: string;
  contract_number: string;
  contract_type: string | null;
  start_date: string | null;
  end_date: string | null;
  sign_date: string | null;
  status: string;
  base_salary: number | null;
  position_id: string | null;
  predecessor_contract_id: string | null;
  created_at: string;
  updated_at: string;
}

export interface CreateContractPayload {
  contract_number: string;
  contract_type?: string;
  start_date?: string;
  end_date?: string;
  sign_date?: string;
  base_salary?: number;
  position_id?: string;
}
