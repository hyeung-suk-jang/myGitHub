// ============================================================
// Route Path Constants
// ============================================================

export const ROUTE = {
  // Root
  ROOT: '/',

  // Auth
  LOGIN:           '/login',
  SIGNUP:          '/signup',
  FORGOT_PASSWORD: '/forgot-password',

  // Dashboard
  DASHBOARD: '/dashboard',
  ANALYTICS: '/dashboard/analytics',

  // User
  USER_LIST:   '/users',
  USER_DETAIL: (id: number | string) => `/users/${id}`,
  USER_CREATE: '/users/create',

  // Error
  NOT_FOUND:    '/404',
  FORBIDDEN:    '/403',
  SERVER_ERROR: '/500',
} as const;
