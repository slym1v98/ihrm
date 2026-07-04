export interface Branch {
  id: string;
  code: string;
  name: string;
  address: string | null;
  phone: string | null;
  email: string | null;
  status: string;
  created_at: string;
  updated_at: string;
}

export interface CreateBranchPayload {
  code: string;
  name: string;
  address?: string;
  phone?: string;
  email?: string;
}

export interface UpdateBranchPayload {
  name: string;
  address?: string;
  phone?: string;
  email?: string;
}
