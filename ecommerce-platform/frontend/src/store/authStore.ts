import { create } from 'zustand';
import { User, LoginData, RegisterData, AuthResponse } from '../types';
import api from '../lib/api';

interface AuthState {
  user: User | null;
  token: string | null;
  isAuthenticated: boolean;
  loading: boolean;
  error: string | null;
  login: (data: LoginData) => Promise<void>;
  register: (data: RegisterData) => Promise<void>;
  logout: () => void;
  initialize: () => void;
}

export const useAuthStore = create<AuthState>((set) => ({
  user: null,
  token: null,
  isAuthenticated: false,
  loading: false,
  error: null,

  initialize: () => {
    const token = localStorage.getItem('token');
    const userStr = localStorage.getItem('user');
    if (token && userStr) {
      const user = JSON.parse(userStr);
      set({ user, token, isAuthenticated: true });
    }
  },

  login: async (data: LoginData) => {
    set({ loading: true, error: null });
    try {
      const response = await api.post<AuthResponse>('/auth/login', data);
      const { user, token } = response.data;

      localStorage.setItem('token', token);
      localStorage.setItem('user', JSON.stringify(user));

      set({ user, token, isAuthenticated: true, loading: false });
    } catch (error: any) {
      set({
        error: error.response?.data?.error || '로그인에 실패했습니다.',
        loading: false,
      });
      throw error;
    }
  },

  register: async (data: RegisterData) => {
    set({ loading: true, error: null });
    try {
      const response = await api.post<AuthResponse>('/auth/register', data);
      const { user, token } = response.data;

      localStorage.setItem('token', token);
      localStorage.setItem('user', JSON.stringify(user));

      set({ user, token, isAuthenticated: true, loading: false });
    } catch (error: any) {
      set({
        error: error.response?.data?.error || '회원가입에 실패했습니다.',
        loading: false,
      });
      throw error;
    }
  },

  logout: () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    set({ user: null, token: null, isAuthenticated: false });
  },
}));
