import { http } from '@/core/http/client';
import type { NotificationMessage } from '@/domains/notification/models/notification';

interface ApiListResponse<T> { data: T[] }
interface ApiOneResponse<T> { data: T }

export const notificationService = {
  async getNotifications(): Promise<NotificationMessage[]> {
    const r = await http.get<ApiListResponse<NotificationMessage>>('/notifications?per_page=10');
    return r.data.data;
  },
  async getUnreadCount(): Promise<number> {
    const r = await http.get<{ data: { count: number } }>('/notifications/unread-count');
    return r.data.data.count;
  },
  async markRead(id: string): Promise<void> {
    await http.patch(`/notifications/${id}/read`);
  },
  async markAllRead(): Promise<void> {
    await http.patch('/notifications/read-all');
  },
};
