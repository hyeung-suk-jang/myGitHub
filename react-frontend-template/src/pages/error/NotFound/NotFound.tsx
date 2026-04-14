import { useNavigate } from 'react-router-dom';
import styles from './NotFound.module.scss';
import { Button } from '@/components/common/Button';
import { ROUTE } from '@/constants/route';

export function NotFound() {
  const navigate = useNavigate();

  return (
    <div className={styles.container}>
      <p className={styles.code}>404</p>
      <h1 className={styles.title}>페이지를 찾을 수 없습니다</h1>
      <p className={styles.description}>요청하신 페이지가 존재하지 않거나 이동되었습니다.</p>
      <Button onClick={() => navigate(ROUTE.DASHBOARD)}>홈으로 이동</Button>
    </div>
  );
}
