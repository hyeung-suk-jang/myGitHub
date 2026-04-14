// ============================================================
// User Store (Redux Toolkit Slice)
// ============================================================

import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import type { PayloadAction } from '@reduxjs/toolkit';
import {
  getUserList,
  getUserDetail,
  createUser,
  updateUser,
  deleteUser,
} from '@/services/modules/user.api';
import type {
  User,
  CreateUserRequest,
  UpdateUserRequest,
  UserListParams,
} from '@/services/types/user.type';
import type { LoadingStatus, PaginationMeta } from '@/types/common.type';

interface UserState {
  users:          User[];
  selectedUser:   User | null;
  pagination:     PaginationMeta;
  status:         LoadingStatus;
  error:          string | null;
}

const initialState: UserState = {
  users:        [],
  selectedUser: null,
  pagination:   { page: 1, pageSize: 20, totalCount: 0, totalPages: 0 },
  status:       'idle',
  error:        null,
};

export const fetchUserList = createAsyncThunk(
  'user/fetchList',
  async (params?: UserListParams) => {
    return getUserList(params);
  },
);

export const fetchUserDetail = createAsyncThunk(
  'user/fetchDetail',
  async (id: number) => {
    return getUserDetail(id);
  },
);

export const createUserThunk = createAsyncThunk(
  'user/create',
  async (data: CreateUserRequest) => {
    return createUser(data);
  },
);

export const updateUserThunk = createAsyncThunk(
  'user/update',
  async ({ id, data }: { id: number; data: UpdateUserRequest }) => {
    return updateUser(id, data);
  },
);

export const deleteUserThunk = createAsyncThunk(
  'user/delete',
  async (id: number) => {
    await deleteUser(id);
    return id;
  },
);

const userSlice = createSlice({
  name: 'user',
  initialState,
  reducers: {
    clearSelectedUser(state) {
      state.selectedUser = null;
    },
    setSelectedUser(state, action: PayloadAction<User>) {
      state.selectedUser = action.payload;
    },
  },
  extraReducers: (builder) => {
    builder
      // fetchUserList
      .addCase(fetchUserList.pending, (state) => {
        state.status = 'loading';
        state.error  = null;
      })
      .addCase(fetchUserList.fulfilled, (state, action) => {
        state.status     = 'succeeded';
        state.users      = action.payload.data;
        state.pagination = action.payload.meta;
      })
      .addCase(fetchUserList.rejected, (state, action) => {
        state.status = 'failed';
        state.error  = action.error.message ?? '사용자 목록 조회에 실패했습니다.';
      })

      // fetchUserDetail
      .addCase(fetchUserDetail.fulfilled, (state, action) => {
        state.selectedUser = action.payload.data;
      })

      // createUserThunk
      .addCase(createUserThunk.fulfilled, (state, action) => {
        state.users.unshift(action.payload.data);
      })

      // updateUserThunk
      .addCase(updateUserThunk.fulfilled, (state, action) => {
        const index = state.users.findIndex((u) => u.id === action.payload.data.id);
        if (index !== -1) {
          state.users[index] = action.payload.data;
        }
        state.selectedUser = action.payload.data;
      })

      // deleteUserThunk
      .addCase(deleteUserThunk.fulfilled, (state, action) => {
        state.users = state.users.filter((u) => u.id !== action.payload);
      });
  },
});

export const { clearSelectedUser, setSelectedUser } = userSlice.actions;
export default userSlice.reducer;
