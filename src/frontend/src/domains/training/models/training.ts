export interface TrainingCourse {
  id: string;
  code: string;
  name: string;
  description: string | null;
  category: string | null;
  default_duration_hours: number;
  max_participants: number;
  active: boolean;
}

export interface CreateCoursePayload {
  code: string;
  name: string;
  description?: string;
  category?: string;
  default_duration_hours?: number;
  max_participants?: number;
}
