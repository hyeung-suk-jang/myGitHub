import { Navigate, Outlet, useLocation } from 'react-router-dom';
import { useAppSelector } from '@/store';
import { ROUTE } from '@/constants/route';

export function PrivateRoute() {
  const isAuthenticated = useAppSelector((s) => s.auth.isAuthenticated);
  const location        = useLocation();

  if (!isAuthenticated) {
    return <Navigate to={ROUTE.LOGIN} state={{ from: location }} replace />;
  }

  return <Outlet />;
}
