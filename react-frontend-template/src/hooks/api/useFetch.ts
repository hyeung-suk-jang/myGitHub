import { useState, useEffect, useRef } from 'react';
import client from '@/services/api/client';
import type { AxiosRequestConfig } from 'axios';

interface FetchState<T> {
  data:      T | null;
  isLoading: boolean;
  isError:   boolean;
  error:     Error | null;
}

export function useFetch<T>(
  url:     string,
  config?: AxiosRequestConfig,
): FetchState<T> {
  const [state, setState] = useState<FetchState<T>>({
    data:      null,
    isLoading: true,
    isError:   false,
    error:     null,
  });

  const isMounted = useRef(true);

  useEffect(() => {
    isMounted.current = true;

    const controller = new AbortController();

    client
      .get<T>(url, { ...config, signal: controller.signal })
      .then((response) => {
        if (isMounted.current) {
          setState({ data: response.data, isLoading: false, isError: false, error: null });
        }
      })
      .catch((err: Error) => {
        if (isMounted.current && err.name !== 'CanceledError') {
          setState({ data: null, isLoading: false, isError: true, error: err });
        }
      });

    return () => {
      isMounted.current = false;
      controller.abort();
    };
  // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [url]);

  return state;
}
