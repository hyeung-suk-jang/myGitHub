import { Suspense } from 'react';
import { createBrowserRouter, Navigate } from 'react-router-dom';
import { MainLayout }  from '@/layouts/MainLayout';
import { AuthLayout }  from '@/layouts/AuthLayout';
import { EmptyLayout } from '@/layouts/EmptyLayout';
import { PrivateRoute } from './PrivateRoute';
import { PublicRoute }  from './PublicRoute';
import { ROUTE } from '@/constants/route';
import {
  LazyDashboardHome,
  LazyAnalytics,
  LazyUserList,
  LazyUserDetail,
  LazyUserCreate,
  LazyLogin,
  LazySignup,
  LazyForgotPassword,
  LazyNotFound,
  LazyForbidden,
  LazyServerError,
} from './routeConfig';

const withSuspense = (Component: React.ComponentType) => (
  <Suspense fallback={<div style={{ padding: '24px', color: '#6b7280' }}>불러오는 중...</div>}>
    <Component />
  </Suspense>
);

export const router = createBrowserRouter([
  {
    path: ROUTE.ROOT,
    element: <Navigate to={ROUTE.DASHBOARD} replace />,
  },

  // Private routes (인증 필요)
  {
    element: <PrivateRoute />,
    children: [
      {
        element: <MainLayout />,
        children: [
          { path: ROUTE.DASHBOARD,             element: withSuspense(LazyDashboardHome) },
          { path: ROUTE.ANALYTICS,             element: withSuspense(LazyAnalytics) },
          { path: ROUTE.USER_LIST,             element: withSuspense(LazyUserList) },
          { path: '/users/:id',                element: withSuspense(LazyUserDetail) },
          { path: ROUTE.USER_CREATE,           element: withSuspense(LazyUserCreate) },
        ],
      },
    ],
  },

  // Public routes (비인증 전용)
  {
    element: <PublicRoute />,
    children: [
      {
        element: <AuthLayout />,
        children: [
          { path: ROUTE.LOGIN,           element: withSuspense(LazyLogin) },
          { path: ROUTE.SIGNUP,          element: withSuspense(LazySignup) },
          { path: ROUTE.FORGOT_PASSWORD, element: withSuspense(LazyForgotPassword) },
        ],
      },
    ],
  },

  // Error pages
  {
    element: <EmptyLayout />,
    children: [
      { path: ROUTE.FORBIDDEN,    element: withSuspense(LazyForbidden) },
      { path: ROUTE.SERVER_ERROR, element: withSuspense(LazyServerError) },
      { path: '*',                element: withSuspense(LazyNotFound) },
    ],
  },
]);
