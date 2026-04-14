// ============================================================
// Axios Interceptors
// ============================================================

import type { AxiosInstance } from 'axios';
import { ACCESS_TOKEN_KEY } from '@/constants/config';
import { handleApiError } from '../error/errorHandler';
import { getLocalStorage } from '@/utils/storage/localStorage';

export function setupInterceptors(instance: AxiosInstance): void {
  // Request interceptor
  instance.interceptors.request.use(
    (config) => {
      const token = getLocalStorage<string>(ACCESS_TOKEN_KEY);
      if (token) {
        config.headers.Authorization = `Bearer ${token}`;
      }
      return config;
    },
    (error) => Promise.reject(error),
  );

  // Response interceptor
  instance.interceptors.response.use(
    (response) => {
      // 비즈니스 에러 처리
      if (response.data?.success === false) {
        return Promise.reject(response.data);
      }
      return response;
    },
    (error) => {
      handleApiError(error);
      return Promise.reject(error);
    },
  );
}
