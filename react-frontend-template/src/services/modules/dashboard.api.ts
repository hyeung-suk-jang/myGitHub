// ============================================================
// Dashboard API Module
// ============================================================

import client from '../api/client';
import { DASHBOARD_API } from '@/constants/api';
import type { ApiResponse } from '../types/common.type';

export interface DashboardSummary {
  totalUsers: number;
  activeUsers: number;
  newUsersToday: number;
  revenue: number;
}

export interface AnalyticsData {
  date: string;
  visitors: number;
  pageViews: number;
  revenue: number;
}

export async function getDashboardSummary() {
  const response = await client.get<ApiResponse<DashboardSummary>>(DASHBOARD_API.SUMMARY);
  return response.data;
}

export async function getAnalytics(params?: { startDate?: string; endDate?: string }) {
  const response = await client.get<ApiResponse<AnalyticsData[]>>(
    DASHBOARD_API.ANALYTICS,
    { params },
  );
  return response.data;
}
