import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import type { User } from '../types';

interface AuthState {
  user: User | null;
  isAuthenticated: boolean;
  login: (email: string, password: string) => Promise<boolean>;
  register: (username: string, email: string, phone: string, password: string) => Promise<boolean>;
  logout: () => void;
}

// Mock users data (실제 환경에서는 백엔드 API 사용)
const mockUsers: User[] = [
  {
    id: '1',
    username: 'admin',
    email: 'admin@studycafe.com',
    phone: '010-1234-5678',
    password: 'admin123',
    isAdmin: true,
    createdAt: new Date(),
  },
  {
    id: '2',
    username: '홍길동',
    email: 'hong@example.com',
    phone: '010-9876-5432',
    password: 'user123',
    isAdmin: false,
    createdAt: new Date(),
  },
];

export const useAuthStore = create<AuthState>()(
  persist(
    (set) => ({
      user: null,
      isAuthenticated: false,

      login: async (email: string, password: string) => {
        // Mock 로그인 (실제 환경에서는 API 호출)
        const user = mockUsers.find(
          (u) => u.email === email && u.password === password
        );

        if (user) {
          set({ user, isAuthenticated: true });
          return true;
        }
        return false;
      },

      register: async (username: string, email: string, phone: string, password: string) => {
        // Mock 회원가입 (실제 환경에서는 API 호출)
        const existingUser = mockUsers.find((u) => u.email === email);
        if (existingUser) {
          return false;
        }

        const newUser: User = {
          id: String(mockUsers.length + 1),
          username,
          email,
          phone,
          password,
          isAdmin: false,
          createdAt: new Date(),
        };

        mockUsers.push(newUser);
        set({ user: newUser, isAuthenticated: true });
        return true;
      },

      logout: () => {
        set({ user: null, isAuthenticated: false });
      },
    }),
    {
      name: 'auth-storage',
    }
  )
);
