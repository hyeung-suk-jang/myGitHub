import { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import styles from './UserList.module.scss';
import { Table } from '@/components/common/Table';
import type { TableColumn } from '@/components/common/Table';
import { Button } from '@/components/common/Button';
import { useAppDispatch, useAppSelector } from '@/store';
import { fetchUserList } from '@/store/modules/user.store';
import type { User } from '@/services/types/user.type';
import { ROUTE } from '@/constants/route';
import { formatDate } from '@/utils';

const columns: TableColumn<User>[] = [
  { key: 'id',        header: 'ID',     width: '60px',  align: 'center' },
  { key: 'name',      header: '이름' },
  { key: 'email',     header: '이메일' },
  { key: 'role',      header: '역할',   width: '100px', align: 'center' },
  { key: 'status',    header: '상태',   width: '100px', align: 'center' },
  {
    key: 'createdAt',
    header: '가입일',
    width: '140px',
    render: (row) => formatDate(row.createdAt),
  },
];

export function UserList() {
  const dispatch  = useAppDispatch();
  const navigate  = useNavigate();
  const { users, status } = useAppSelector((s) => s.user);

  useEffect(() => {
    dispatch(fetchUserList());
  }, [dispatch]);

  return (
    <div className={styles.container}>
      <div className={styles.header}>
        <h1 className={styles.title}>사용자 관리</h1>
        <Button onClick={() => navigate(ROUTE.USER_CREATE)}>새 사용자 등록</Button>
      </div>

      <Table
        columns={columns}
        data={users}
        keyField="id"
        isLoading={status === 'loading'}
        emptyText="등록된 사용자가 없습니다."
      />
    </div>
  );
}
