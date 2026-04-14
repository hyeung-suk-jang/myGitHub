import { useCallback } from 'react';
import { useAppDispatch } from '@/store';
import { addToast } from '@/store/modules/ui.store';
import { ERROR_MESSAGE } from '@/constants/message';

export function useErrorHandler() {
  const dispatch = useAppDispatch();

  const handleError = useCallback(
    (error: unknown, fallbackMessage?: string) => {
      console.error('[useErrorHandler]', error);

      let message = fallbackMessage ?? ERROR_MESSAGE.UNKNOWN;

      if (error instanceof Error) {
        message = error.message || message;
      }

      dispatch(addToast({ message, type: 'error' }));
    },
    [dispatch],
  );

  return { handleError };
}
