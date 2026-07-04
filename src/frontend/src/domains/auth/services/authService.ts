import { http } from '@/core/http/client';
import type { ApiResponse, LoginResponse, RoleDetail, User } from '@/domains/auth/models/auth';

export const authService = {
  async login(email: string, password: string) {
    const response = await http.post<ApiResponse<LoginResponse>>('/auth/login', { email, password });
    return response.data.data;
  },

  async me() {
    const response = await http.get<ApiResponse<User>>('/auth/me');
    return response.data.data;
  },

  async logout() {
    await http.post<ApiResponse<null>>('/auth/logout');
  },

  async role(id: string) {
    const response = await http.get<ApiResponse<RoleDetail>>(`/roles/${id}`);
    return response.data.data;
  },
};
