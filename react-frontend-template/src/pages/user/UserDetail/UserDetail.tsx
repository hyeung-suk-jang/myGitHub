import { useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import styles from './UserDetail.module.scss';
import { Button } from '@/components/common/Button';
import { useAppDispatch, useAppSelector } from '@/store';
import { fetchUserDetail, deleteUserThunk } from '@/store/modules/user.store';
import { ROUTE } from '@/constants/route';
import { formatDateTime } from '@/utils';

export function UserDetail() {
  const { id }     = useParams<{ id: string }>();
  const dispatch   = useAppDispatch();
  const navigate   = useNavigate();
  const user       = useAppSelector((s) => s.user.selectedUser);

  useEffect(() => {
    if (id) dispatch(fetchUserDetail(Number(id)));
  }, [id, dispatch]);

  const handleDelete = async () => {
    if (!user || !confirm('사용자를 삭제하시겠습니까?')) return;
    await dispatch(deleteUserThunk(user.id));
    navigate(ROUTE.USER_LIST);
  };

  if (!user) return <p className={styles.loading}>불러오는 중...</p>;

  return (
    <div className={styles.container}>
      <div className={styles.header}>
        <h1 className={styles.title}>사용자 상세</h1>
        <div className={styles.actions}>
          <Button variant="outline" onClick={() => navigate(ROUTE.USER_LIST)}>목록</Button>
          <Button variant="danger" onClick={handleDelete}>삭제</Button>
        </div>
      </div>

      <div className={styles.card}>
        {([
          ['이름',    user.name],
          ['이메일',  user.email],
          ['역할',    user.role],
          ['상태',    user.status],
          ['가입일',  formatDateTime(user.createdAt)],
          ['수정일',  formatDateTime(user.updatedAt)],
        ] as [string, string][]).map(([label, value]) => (
          <div key={label} className={styles.row}>
            <dt className={styles.label}>{label}</dt>
            <dd className={styles.value}>{value}</dd>
          </div>
        ))}
      </div>
    </div>
  );
}
