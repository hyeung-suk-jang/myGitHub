import { useState, useEffect, useCallback, useRef } from 'react';

interface QueryState<T> {
  data:      T | null;
  isLoading: boolean;
  isError:   boolean;
  error:     Error | null;
}

interface UseQueryOptions {
  enabled?: boolean;
}

interface UseQueryReturn<T> extends QueryState<T> {
  refetch: () => void;
}

export function useQuery<T>(
  queryFn:  () => Promise<T>,
  deps:     unknown[] = [],
  options:  UseQueryOptions = {},
): UseQueryReturn<T> {
  const { enabled = true } = options;

  const [state, setState] = useState<QueryState<T>>({
    data:      null,
    isLoading: false,
    isError:   false,
    error:     null,
  });

  const isMounted = useRef(true);
  const queryFnRef = useRef(queryFn);
  queryFnRef.current = queryFn;

  const execute = useCallback(async () => {
    if (!enabled) return;

    setState((prev) => ({ ...prev, isLoading: true, isError: false, error: null }));

    try {
      const data = await queryFnRef.current();
      if (isMounted.current) {
        setState({ data, isLoading: false, isError: false, error: null });
      }
    } catch (err) {
      if (isMounted.current) {
        setState({
          data:      null,
          isLoading: false,
          isError:   true,
          error:     err instanceof Error ? err : new Error('Unknown error'),
        });
      }
    }
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [enabled, ...deps]);

  useEffect(() => {
    isMounted.current = true;
    execute();
    return () => { isMounted.current = false; };
  }, [execute]);

  return { ...state, refetch: execute };
}
