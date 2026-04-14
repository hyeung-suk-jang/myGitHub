import { useState, useCallback } from 'react';

interface MutationState<T> {
  data:      T | null;
  isLoading: boolean;
  isError:   boolean;
  error:     Error | null;
}

interface UseMutationOptions<T> {
  onSuccess?: (data: T) => void;
  onError?:   (error: Error) => void;
}

interface UseMutationReturn<T, V> extends MutationState<T> {
  mutate: (variables: V) => Promise<void>;
  reset:  () => void;
}

export function useMutation<T, V = void>(
  mutationFn: (variables: V) => Promise<T>,
  options:    UseMutationOptions<T> = {},
): UseMutationReturn<T, V> {
  const { onSuccess, onError } = options;

  const [state, setState] = useState<MutationState<T>>({
    data:      null,
    isLoading: false,
    isError:   false,
    error:     null,
  });

  const mutate = useCallback(
    async (variables: V) => {
      setState({ data: null, isLoading: true, isError: false, error: null });

      try {
        const data = await mutationFn(variables);
        setState({ data, isLoading: false, isError: false, error: null });
        onSuccess?.(data);
      } catch (err) {
        const error = err instanceof Error ? err : new Error('Unknown error');
        setState({ data: null, isLoading: false, isError: true, error });
        onError?.(error);
      }
    },
    [mutationFn, onSuccess, onError],
  );

  const reset = useCallback(() => {
    setState({ data: null, isLoading: false, isError: false, error: null });
  }, []);

  return { ...state, mutate, reset };
}
