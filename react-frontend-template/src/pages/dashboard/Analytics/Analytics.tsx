import styles from './Analytics.module.scss';
import { useQuery } from '@/hooks/api/useQuery';
import { getAnalytics } from '@/services/modules/dashboard.api';
import { formatDate, formatNumber } from '@/utils';

export function Analytics() {
  const { data, isLoading } = useQuery(() => getAnalytics());

  return (
    <div className={styles.container}>
      <h1 className={styles.title}>분석</h1>

      {isLoading ? (
        <p className={styles.loading}>불러오는 중...</p>
      ) : (
        <div className={styles.tableWrapper}>
          <table className={styles.table}>
            <thead>
              <tr>
                <th>날짜</th>
                <th>방문자</th>
                <th>페이지뷰</th>
                <th>매출</th>
              </tr>
            </thead>
            <tbody>
              {data?.data?.map((row) => (
                <tr key={row.date}>
                  <td>{formatDate(row.date)}</td>
                  <td>{formatNumber(row.visitors)}</td>
                  <td>{formatNumber(row.pageViews)}</td>
                  <td>{formatNumber(row.revenue)}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
