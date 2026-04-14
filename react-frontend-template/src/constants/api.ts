// ============================================================
// API URL Constants
// ============================================================

export const AUTH_API = {
  LOGIN:           '/api/auth/login',
  LOGOUT:          '/api/auth/logout',
  REFRESH:         '/api/auth/refresh',
  SIGNUP:          '/api/auth/signup',
  FORGOT_PASSWORD: '/api/auth/forgot-password',
} as const;

export const USER_API = {
  BASE:   '/api/users',
  DETAIL: (id: number) => `/api/users/${id}`,
  ME:     '/api/users/me',
} as const;

export const DASHBOARD_API = {
  SUMMARY:   '/api/dashboard/summary',
  ANALYTICS: '/api/dashboard/analytics',
} as const;
