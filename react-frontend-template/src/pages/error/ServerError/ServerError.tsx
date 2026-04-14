import { useNavigate } from 'react-router-dom';
import styles from './ServerError.module.scss';
import { Button } from '@/components/common/Button';
import { ROUTE } from '@/constants/route';

export function ServerError() {
  const navigate = useNavigate();

  return (
    <div className={styles.container}>
      <p className={styles.code}>500</p>
      <h1 className={styles.title}>서버 오류가 발생했습니다</h1>
      <p className={styles.description}>일시적인 서버 오류입니다. 잠시 후 다시 시도해 주세요.</p>
      <div className={styles.actions}>
        <Button variant="outline" onClick={() => window.location.reload()}>새로고침</Button>
        <Button onClick={() => navigate(ROUTE.DASHBOARD)}>홈으로 이동</Button>
      </div>
    </div>
  );
}
