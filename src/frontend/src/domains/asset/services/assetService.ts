import { http } from '@/core/http/client';
import type { AssetItem, AssetAssignment, CreateAssetItemPayload, AssignAssetPayload } from '@/domains/asset/models/asset';

interface ApiListResponse<T> {
  data: T[];
}

export const assetService = {
  async getItems(): Promise<AssetItem[]> {
    const res = await http.get<ApiListResponse<AssetItem>>('/assets/items');
    return res.data.data;
  },

  async getItem(id: string): Promise<AssetItem> {
    const res = await http.get<{ data: AssetItem }>(`/assets/items/${id}`);
    return res.data.data;
  },

  async createItem(payload: CreateAssetItemPayload): Promise<AssetItem> {
    const res = await http.post<{ data: AssetItem }>('/assets/items', payload);
    return res.data.data;
  },

  async updateItem(id: string, payload: Partial<CreateAssetItemPayload>): Promise<AssetItem> {
    const res = await http.put<{ data: AssetItem }>(`/assets/items/${id}`, payload);
    return res.data.data;
  },

  async deleteItem(id: string): Promise<void> {
    await http.delete(`/assets/items/${id}`);
  },

  async markStatus(id: string, status: string): Promise<void> {
    const actions: Record<string, string> = {
      available: 'mark-available',
      maintenance: 'mark-maintenance',
      lost: 'mark-lost',
      damaged: 'mark-damaged',
    };
    await http.post(`/assets/items/${id}/${actions[status]}`);
  },

  async getAssignments(): Promise<AssetAssignment[]> {
    const res = await http.get<ApiListResponse<AssetAssignment>>('/assets/assignments');
    return res.data.data;
  },

  async assignAsset(payload: AssignAssetPayload): Promise<AssetAssignment> {
    const res = await http.post<{ data: AssetAssignment }>('/assets/assignments', payload);
    return res.data.data;
  },

  async returnAsset(id: string): Promise<void> {
    await http.post(`/assets/assignments/${id}/return`);
  },
};
