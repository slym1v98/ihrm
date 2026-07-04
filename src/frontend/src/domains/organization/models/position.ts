export interface Position {
  id: string;
  code: string;
  name: string;
  level: number | null;
  description: string | null;
  status: string;
  created_at: string;
  updated_at: string;
}

export interface CreatePositionPayload {
  code: string;
  name: string;
  level?: number;
  description?: string;
}

export interface UpdatePositionPayload {
  name: string;
  level?: number;
  description?: string;
}
