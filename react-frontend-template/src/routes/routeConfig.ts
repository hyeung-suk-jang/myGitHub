// ============================================================
// Route Configuration
// ============================================================

import { lazy } from 'react';

export const LazyDashboardHome = lazy(() =>
  import('@/pages/dashboard/DashboardHome').then((m) => ({ default: m.DashboardHome })),
);
export const LazyAnalytics = lazy(() =>
  import('@/pages/dashboard/Analytics').then((m) => ({ default: m.Analytics })),
);
export const LazyUserList = lazy(() =>
  import('@/pages/user/UserList').then((m) => ({ default: m.UserList })),
);
export const LazyUserDetail = lazy(() =>
  import('@/pages/user/UserDetail').then((m) => ({ default: m.UserDetail })),
);
export const LazyUserCreate = lazy(() =>
  import('@/pages/user/UserCreate').then((m) => ({ default: m.UserCreate })),
);
export const LazyLogin = lazy(() =>
  import('@/pages/auth/Login').then((m) => ({ default: m.Login })),
);
export const LazySignup = lazy(() =>
  import('@/pages/auth/Signup').then((m) => ({ default: m.Signup })),
);
export const LazyForgotPassword = lazy(() =>
  import('@/pages/auth/ForgotPassword').then((m) => ({ default: m.ForgotPassword })),
);
export const LazyNotFound    = lazy(() =>
  import('@/pages/error/NotFound').then((m) => ({ default: m.NotFound })),
);
export const LazyForbidden   = lazy(() =>
  import('@/pages/error/Forbidden').then((m) => ({ default: m.Forbidden })),
);
export const LazyServerError = lazy(() =>
  import('@/pages/error/ServerError').then((m) => ({ default: m.ServerError })),
);
