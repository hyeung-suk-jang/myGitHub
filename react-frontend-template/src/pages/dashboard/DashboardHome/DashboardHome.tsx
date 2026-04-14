import styles from './DashboardHome.module.scss';
import { useQuery } from '@/hooks/api/useQuery';
import { getDashboardSummary } from '@/services/modules/dashboard.api';
import { formatNumber, formatKRW } from '@/utils';

export function DashboardHome() {
  const { data, isLoading } = useQuery(() => getDashboardSummary());

  const summaryItems = data?.data
    ? [
        { label: '전체 사용자',  value: formatNumber(data.data.totalUsers) },
        { label: '활성 사용자',  value: formatNumber(data.data.activeUsers) },
        { label: '오늘 신규',    value: formatNumber(data.data.newUsersToday) },
        { label: '매출',         value: formatKRW(data.data.revenue) },
      ]
    : [];

  return (
    <div className={styles.container}>
      <h1 className={styles.title}>대시보드</h1>

      {isLoading ? (
        <div className={styles.loading}>불러오는 중...</div>
      ) : (
        <div className={styles.summaryGrid}>
          {summaryItems.map((item) => (
            <div key={item.label} className={styles.summaryCard}>
              <p className={styles.cardLabel}>{item.label}</p>
              <p className={styles.cardValue}>{item.value}</p>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
