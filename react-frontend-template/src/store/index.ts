// ============================================================
// Redux Store
// ============================================================

import { configureStore } from '@reduxjs/toolkit';
import { TypedUseSelectorHook, useDispatch, useSelector } from 'react-redux';
import authReducer from './modules/auth.store';
import userReducer from './modules/user.store';
import uiReducer   from './modules/ui.store';
import { loggerMiddleware } from './middleware/logger';

export const store = configureStore({
  reducer: {
    auth: authReducer,
    user: userReducer,
    ui:   uiReducer,
  },
  middleware: (getDefaultMiddleware) =>
    getDefaultMiddleware().concat(loggerMiddleware),
});

export type RootState   = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;

export const useAppDispatch: () => AppDispatch = useDispatch;
export const useAppSelector: TypedUseSelectorHook<RootState> = useSelector;
