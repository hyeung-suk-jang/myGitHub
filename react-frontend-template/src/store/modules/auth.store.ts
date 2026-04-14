// ============================================================
// Auth Store (Redux Toolkit Slice)
// ============================================================

import { createSlice, createAsyncThunk } from '@reduxjs/toolkit';
import type { PayloadAction } from '@reduxjs/toolkit';
import { login, logout } from '@/services/modules/auth.api';
import type { LoginRequest } from '@/services/modules/auth.api';
import type { User } from '@/services/types/user.type';
import type { LoadingStatus } from '@/types/common.type';
import {
  setLocalStorage,
  removeLocalStorage,
} from '@/utils/storage/localStorage';
import { ACCESS_TOKEN_KEY, REFRESH_TOKEN_KEY } from '@/constants/config';

interface AuthState {
  user: User | null;
  isAuthenticated: boolean;
  status: LoadingStatus;
  error: string | null;
}

const initialState: AuthState = {
  user:            null,
  isAuthenticated: false,
  status:          'idle',
  error:           null,
};

export const loginThunk = createAsyncThunk(
  'auth/login',
  async (credentials: LoginRequest) => {
    const response = await login(credentials);
    return response.data;
  },
);

export const logoutThunk = createAsyncThunk('auth/logout', async () => {
  await logout();
});

const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    setUser(state, action: PayloadAction<User>) {
      state.user            = action.payload;
      state.isAuthenticated = true;
    },
    clearAuth(state) {
      state.user            = null;
      state.isAuthenticated = false;
      state.status          = 'idle';
      state.error           = null;
      removeLocalStorage(ACCESS_TOKEN_KEY);
      removeLocalStorage(REFRESH_TOKEN_KEY);
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(loginThunk.pending, (state) => {
        state.status = 'loading';
        state.error  = null;
      })
      .addCase(loginThunk.fulfilled, (state, action) => {
        state.status          = 'succeeded';
        state.user            = action.payload.user;
        state.isAuthenticated = true;
        setLocalStorage(ACCESS_TOKEN_KEY, action.payload.accessToken);
        setLocalStorage(REFRESH_TOKEN_KEY, action.payload.refreshToken);
      })
      .addCase(loginThunk.rejected, (state, action) => {
        state.status = 'failed';
        state.error  = action.error.message ?? '로그인에 실패했습니다.';
      })
      .addCase(logoutThunk.fulfilled, (state) => {
        state.user            = null;
        state.isAuthenticated = false;
        state.status          = 'idle';
        removeLocalStorage(ACCESS_TOKEN_KEY);
        removeLocalStorage(REFRESH_TOKEN_KEY);
      });
  },
});

export const { setUser, clearAuth } = authSlice.actions;
export default authSlice.reducer;
