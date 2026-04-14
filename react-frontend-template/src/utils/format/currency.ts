// ============================================================
// Currency Format Utilities
// ============================================================

export function formatKRW(value: number): string {
  return new Intl.NumberFormat('ko-KR', {
    style:    'currency',
    currency: 'KRW',
  }).format(value);
}

export function formatUSD(value: number): string {
  return new Intl.NumberFormat('en-US', {
    style:                 'currency',
    currency:              'USD',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value);
}

export function formatCurrency(
  value:    number,
  currency: string = 'KRW',
  locale:   string = 'ko-KR',
): string {
  return new Intl.NumberFormat(locale, {
    style:    'currency',
    currency,
  }).format(value);
}
