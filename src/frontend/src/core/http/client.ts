import axios from 'axios';
import { config } from '@/core/config';

let accessToken: string | null = null;

export const http = axios.create({
  baseURL: config.apiBaseUrl,
  headers: { Accept: 'application/json' },
});

export function setAccessToken(token: string | null) {
  accessToken = token;
}

http.interceptors.request.use((request) => {
  if (accessToken) request.headers.Authorization = `Bearer ${accessToken}`;
  return request;
});

http.interceptors.response.use(
  (response) => response,
  (error: unknown) => {
    if (axios.isAxiosError(error) && error.response?.status === 401 && typeof window !== 'undefined') {
      document.cookie = `${config.accessTokenCookie}=; path=/; max-age=0; SameSite=Lax`;
      setAccessToken(null);
      if (!window.location.pathname.startsWith('/login')) {
        window.location.assign('/login');
      }
    }

    return Promise.reject(error);
  },
);
