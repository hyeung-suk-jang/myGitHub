// ============================================================
// UI Store (Redux Toolkit Slice)
// ============================================================

import { createSlice } from '@reduxjs/toolkit';
import type { PayloadAction } from '@reduxjs/toolkit';

type Theme = 'light' | 'dark';

interface ToastItem {
  id: string;
  message: string;
  type: 'success' | 'error' | 'warning' | 'info';
}

interface UiState {
  theme:           Theme;
  isSidebarOpen:   boolean;
  isGlobalLoading: boolean;
  toasts:          ToastItem[];
}

const initialState: UiState = {
  theme:           'light',
  isSidebarOpen:   true,
  isGlobalLoading: false,
  toasts:          [],
};

const uiSlice = createSlice({
  name: 'ui',
  initialState,
  reducers: {
    setTheme(state, action: PayloadAction<Theme>) {
      state.theme = action.payload;
      document.documentElement.setAttribute('data-theme', action.payload);
    },
    toggleSidebar(state) {
      state.isSidebarOpen = !state.isSidebarOpen;
    },
    setSidebarOpen(state, action: PayloadAction<boolean>) {
      state.isSidebarOpen = action.payload;
    },
    setGlobalLoading(state, action: PayloadAction<boolean>) {
      state.isGlobalLoading = action.payload;
    },
    addToast(state, action: PayloadAction<Omit<ToastItem, 'id'>>) {
      state.toasts.push({
        id: `toast-${Date.now()}`,
        ...action.payload,
      });
    },
    removeToast(state, action: PayloadAction<string>) {
      state.toasts = state.toasts.filter((t) => t.id !== action.payload);
    },
  },
});

export const {
  setTheme,
  toggleSidebar,
  setSidebarOpen,
  setGlobalLoading,
  addToast,
  removeToast,
} = uiSlice.actions;
export default uiSlice.reducer;
