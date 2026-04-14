import { useState, useCallback } from 'react';
import { DEFAULT_PAGE_SIZE } from '@/constants/config';

interface PaginationState {
  page:     number;
  pageSize: number;
}

interface UsePaginationReturn extends PaginationState {
  setPage:     (page: number) => void;
  setPageSize: (size: number) => void;
  resetPage:   () => void;
}

export function usePagination(
  initialPage:     number = 1,
  initialPageSize: number = DEFAULT_PAGE_SIZE,
): UsePaginationReturn {
  const [pagination, setPagination] = useState<PaginationState>({
    page:     initialPage,
    pageSize: initialPageSize,
  });

  const setPage = useCallback((page: number) => {
    setPagination((prev) => ({ ...prev, page }));
  }, []);

  const setPageSize = useCallback((pageSize: number) => {
    setPagination({ page: 1, pageSize });
  }, []);

  const resetPage = useCallback(() => {
    setPagination((prev) => ({ ...prev, page: 1 }));
  }, []);

  return { ...pagination, setPage, setPageSize, resetPage };
}
