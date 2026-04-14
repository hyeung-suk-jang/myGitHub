// ============================================================
// Date Format Utilities
// ============================================================

export function formatDate(date: string | Date, separator: string = '-'): string {
  const d = new Date(date);
  const year  = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day   = String(d.getDate()).padStart(2, '0');
  return `${year}${separator}${month}${separator}${day}`;
}

export function formatDateTime(date: string | Date): string {
  const d      = new Date(date);
  const dateStr = formatDate(d);
  const hours   = String(d.getHours()).padStart(2, '0');
  const minutes = String(d.getMinutes()).padStart(2, '0');
  const seconds = String(d.getSeconds()).padStart(2, '0');
  return `${dateStr} ${hours}:${minutes}:${seconds}`;
}

export function formatRelativeTime(date: string | Date): string {
  const now   = Date.now();
  const target = new Date(date).getTime();
  const diff  = now - target;

  const MINUTE = 60 * 1_000;
  const HOUR   = 60 * MINUTE;
  const DAY    = 24 * HOUR;
  const WEEK   = 7  * DAY;

  if (diff < MINUTE)  return '방금 전';
  if (diff < HOUR)    return `${Math.floor(diff / MINUTE)}분 전`;
  if (diff < DAY)     return `${Math.floor(diff / HOUR)}시간 전`;
  if (diff < WEEK)    return `${Math.floor(diff / DAY)}일 전`;
  return formatDate(date);
}

export function isValidDate(date: string | Date): boolean {
  const d = new Date(date);
  return !isNaN(d.getTime());
}
