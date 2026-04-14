// ============================================================
// App Configuration Constants
// ============================================================

export const API_BASE_URL = import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8080';
export const APP_TITLE    = import.meta.env.VITE_APP_TITLE    ?? 'React Frontend Template';
export const APP_VERSION  = import.meta.env.VITE_APP_VERSION  ?? '1.0.0';

export const API_TIMEOUT     = 10_000;
export const MAX_RETRY_COUNT = 3;

export const DEFAULT_PAGE_SIZE = 20;
export const MAX_PAGE_SIZE     = 100;

export const ACCESS_TOKEN_KEY  = 'access_token';
export const REFRESH_TOKEN_KEY = 'refresh_token';
