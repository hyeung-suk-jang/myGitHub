import { useEffect } from 'react';
import { createPortal } from 'react-dom';
import styles from './ErrorToast.module.scss';
import { useAppDispatch, useAppSelector } from '@/store';
import { removeToast } from '@/store/modules/ui.store';
import { registerToastFn } from '@/services/error/errorHandler';

const TOAST_DURATION = 4_000;

export function ErrorToast() {
  const dispatch = useAppDispatch();
  const toasts   = useAppSelector((state) => state.ui.toasts);

  useEffect(() => {
    registerToastFn((message) => {
      dispatch({ type: 'ui/addToast', payload: { message, type: 'error' } });
    });
  }, [dispatch]);

  useEffect(() => {
    if (toasts.length === 0) return;

    const timer = setTimeout(() => {
      dispatch(removeToast(toasts[0].id));
    }, TOAST_DURATION);

    return () => clearTimeout(timer);
  }, [toasts, dispatch]);

  if (toasts.length === 0) return null;

  return createPortal(
    <div className={styles.container} role="region" aria-live="polite" aria-label="알림">
      {toasts.map((toast) => (
        <div key={toast.id} className={[styles.toast, styles[toast.type]].join(' ')}>
          <span className={styles.message}>{toast.message}</span>
          <button
            className={styles.closeBtn}
            onClick={() => dispatch(removeToast(toast.id))}
            aria-label="알림 닫기"
          >
            ✕
          </button>
        </div>
      ))}
    </div>,
    document.body,
  );
}
