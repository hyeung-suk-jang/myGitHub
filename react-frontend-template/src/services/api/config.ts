// ============================================================
// Axios Config
// ============================================================

import { API_BASE_URL, API_TIMEOUT } from '@/constants/config';

export const axiosConfig = {
  baseURL: API_BASE_URL,
  timeout: API_TIMEOUT,
  headers: {
    'Content-Type': 'application/json',
  },
} as const;
