// ============================================================
// Auth API Module
// ============================================================

import client from '../api/client';
import { AUTH_API } from '@/constants/api';
import type { ApiResponse } from '../types/common.type';
import type { User } from '../types/user.type';

export interface LoginRequest {
  email: string;
  password: string;
}

export interface LoginResponse {
  accessToken: string;
  refreshToken: string;
  user: User;
}

export interface SignupRequest {
  email: string;
  name: string;
  password: string;
  passwordConfirm: string;
}

export async function login(data: LoginRequest) {
  const response = await client.post<ApiResponse<LoginResponse>>(AUTH_API.LOGIN, data);
  return response.data;
}

export async function logout() {
  const response = await client.post<ApiResponse<null>>(AUTH_API.LOGOUT);
  return response.data;
}

export async function signup(data: SignupRequest) {
  const response = await client.post<ApiResponse<User>>(AUTH_API.SIGNUP, data);
  return response.data;
}

export async function refreshToken(token: string) {
  const response = await client.post<ApiResponse<{ accessToken: string }>>(
    AUTH_API.REFRESH,
    { refreshToken: token },
  );
  return response.data;
}

export async function forgotPassword(email: string) {
  const response = await client.post<ApiResponse<null>>(
    AUTH_API.FORGOT_PASSWORD,
    { email },
  );
  return response.data;
}
