// ============================================================
// Redux Persist Middleware
// ============================================================

import type { Middleware } from '@reduxjs/toolkit';
import { setLocalStorage } from '@/utils/storage/localStorage';

const PERSIST_KEY = 'redux_state';

export const persistMiddleware: Middleware = (store) => (next) => (action) => {
  const result = next(action);

  const stateToSave = {
    ui: (store.getState() as { ui: { theme: string } }).ui.theme,
  };

  setLocalStorage(PERSIST_KEY, stateToSave);

  return result;
};
