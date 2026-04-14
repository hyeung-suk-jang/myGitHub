import { Navigate, Outlet } from 'react-router-dom';
import { useAppSelector } from '@/store';
import { ROUTE } from '@/constants/route';

export function PublicRoute() {
  const isAuthenticated = useAppSelector((s) => s.auth.isAuthenticated);

  if (isAuthenticated) {
    return <Navigate to={ROUTE.DASHBOARD} replace />;
  }

  return <Outlet />;
}
