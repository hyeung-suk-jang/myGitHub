import { useNavigate } from 'react-router-dom';
import styles from './Forbidden.module.scss';
import { Button } from '@/components/common/Button';
import { ROUTE } from '@/constants/route';

export function Forbidden() {
  const navigate = useNavigate();

  return (
    <div className={styles.container}>
      <p className={styles.code}>403</p>
      <h1 className={styles.title}>접근 권한이 없습니다</h1>
      <p className={styles.description}>이 페이지에 접근할 수 있는 권한이 없습니다.</p>
      <Button onClick={() => navigate(ROUTE.DASHBOARD)}>홈으로 이동</Button>
    </div>
  );
}
