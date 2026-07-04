import { http } from '@/core/http/client';
import type { TrainingCourse, CreateCoursePayload } from '@/domains/training/models/training';

interface ApiListResponse<T> { data: T[] }

export const trainingService = {
  async getCourses(): Promise<TrainingCourse[]> {
    const res = await http.get<ApiListResponse<TrainingCourse>>('/training/courses');
    return res.data.data;
  },
  async createCourse(payload: CreateCoursePayload): Promise<TrainingCourse> {
    const res = await http.post<{ data: TrainingCourse }>('/training/courses', payload);
    return res.data.data;
  },
  async updateCourse(id: string, payload: Partial<CreateCoursePayload>): Promise<TrainingCourse> {
    const res = await http.put<{ data: TrainingCourse }>(`/training/courses/${id}`, payload);
    return res.data.data;
  },
  async deleteCourse(id: string): Promise<void> {
    await http.delete(`/training/courses/${id}`);
  },
};
