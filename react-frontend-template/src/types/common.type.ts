// ============================================================
// Common Types
// ============================================================

export type Nullable<T> = T | null;
export type Optional<T> = T | undefined;

export interface SelectOption<T = string> {
  label: string;
  value: T;
  disabled?: boolean;
}

export interface PaginationMeta {
  page: number;
  pageSize: number;
  totalCount: number;
  totalPages: number;
}

export interface SortConfig {
  field: string;
  direction: 'asc' | 'desc';
}

export type LoadingStatus = 'idle' | 'loading' | 'succeeded' | 'failed';

export interface BaseEntity {
  id: number;
  createdAt: string;
  updatedAt: string;
}
