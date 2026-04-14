// ============================================================
// Throttle Utility
// ============================================================

export function throttle<T extends (...args: unknown[]) => unknown>(
  fn:    T,
  limit: number,
): (...args: Parameters<T>) => void {
  let lastCall = 0;

  return (...args: Parameters<T>) => {
    const now = Date.now();
    if (now - lastCall >= limit) {
      lastCall = now;
      fn(...args);
    }
  };
}
