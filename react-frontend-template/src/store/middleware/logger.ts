// ============================================================
// Redux Logger Middleware (dev only)
// ============================================================

import type { Middleware } from '@reduxjs/toolkit';

export const loggerMiddleware: Middleware = (store) => (next) => (action) => {
  if (import.meta.env.DEV) {
    console.group(`[Redux] ${String((action as { type: string }).type)}`);
    console.log('prev state:', store.getState());
    console.log('action:',     action);
  }

  const result = next(action);

  if (import.meta.env.DEV) {
    console.log('next state:', store.getState());
    console.groupEnd();
  }

  return result;
};
