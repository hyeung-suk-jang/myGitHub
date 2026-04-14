// ============================================================
// Session Storage Utilities
// ============================================================

export function setSessionStorage<T>(key: string, value: T): void {
  try {
    sessionStorage.setItem(key, JSON.stringify(value));
  } catch (error) {
    console.error('[sessionStorage] set error:', error);
  }
}

export function getSessionStorage<T>(key: string): T | null {
  try {
    const item = sessionStorage.getItem(key);
    return item ? (JSON.parse(item) as T) : null;
  } catch (error) {
    console.error('[sessionStorage] get error:', error);
    return null;
  }
}

export function removeSessionStorage(key: string): void {
  try {
    sessionStorage.removeItem(key);
  } catch (error) {
    console.error('[sessionStorage] remove error:', error);
  }
}

export function clearSessionStorage(): void {
  try {
    sessionStorage.clear();
  } catch (error) {
    console.error('[sessionStorage] clear error:', error);
  }
}
