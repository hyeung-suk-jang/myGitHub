// ============================================================
// User API Module
// ============================================================

import client from '../api/client';
import { USER_API } from '@/constants/api';
import type { ApiResponse, ApiListResponse } from '../types/common.type';
import type {
  User,
  CreateUserRequest,
  UpdateUserRequest,
  UserListParams,
} from '../types/user.type';

export async function getUserList(params?: UserListParams) {
  const response = await client.get<ApiListResponse<User>>(USER_API.BASE, { params });
  return response.data;
}

export async function getUserDetail(id: number) {
  const response = await client.get<ApiResponse<User>>(USER_API.DETAIL(id));
  return response.data;
}

export async function createUser(data: CreateUserRequest) {
  const response = await client.post<ApiResponse<User>>(USER_API.BASE, data);
  return response.data;
}

export async function updateUser(id: number, data: UpdateUserRequest) {
  const response = await client.put<ApiResponse<User>>(USER_API.DETAIL(id), data);
  return response.data;
}

export async function deleteUser(id: number) {
  const response = await client.delete<ApiResponse<null>>(USER_API.DETAIL(id));
  return response.data;
}

export async function getMe() {
  const response = await client.get<ApiResponse<User>>(USER_API.ME);
  return response.data;
}
