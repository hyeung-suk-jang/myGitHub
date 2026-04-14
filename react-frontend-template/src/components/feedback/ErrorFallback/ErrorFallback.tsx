import styles from './ErrorFallback.module.scss';
import { Button } from '@/components/common/Button';

interface ErrorFallbackProps {
  error?:   Error;
  onReset?: () => void;
}

export function ErrorFallback({ error, onReset }: ErrorFallbackProps) {
  return (
    <div className={styles.container} role="alert">
      <div className={styles.icon}>⚠️</div>
      <h2 className={styles.title}>오류가 발생했습니다</h2>
      <p className={styles.message}>
        {error?.message ?? '알 수 없는 오류가 발생했습니다. 잠시 후 다시 시도해 주세요.'}
      </p>
      <div className={styles.actions}>
        <Button variant="outline" onClick={() => window.location.reload()}>
          페이지 새로고침
        </Button>
        {onReset && (
          <Button variant="primary" onClick={onReset}>
            다시 시도
          </Button>
        )}
      </div>
    </div>
  );
}
