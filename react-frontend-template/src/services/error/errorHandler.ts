// ============================================================
// Central Error Handler
// ============================================================

import type { AxiosError } from 'axios';
import { ERROR_CODE } from './errorCode';
import { getErrorMessage } from './errorMessage';
import { ERROR_MESSAGE } from '@/constants/message';
import { ROUTE } from '@/constants/route';

type ToastFn = (message: string) => void;

let showToast: ToastFn = (message) => console.error('[Toast]', message);

export function registerToastFn(fn: ToastFn): void {
  showToast = fn;
}

export function handleApiError(error: AxiosError): void {
  if (!error.response) {
    showToast(ERROR_MESSAGE.NETWORK);
    return;
  }

  const { status } = error.response;

  if (status === ERROR_CODE.UNAUTHORIZED) {
    showToast(getErrorMessage(status));
    window.location.href = ROUTE.LOGIN;
    return;
  }

  showToast(getErrorMessage(status));
}
