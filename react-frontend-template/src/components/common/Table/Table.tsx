import type { ReactNode } from 'react';
import styles from './Table.module.scss';

export interface TableColumn<T> {
  key:       string;
  header:    ReactNode;
  render?:   (row: T, index: number) => ReactNode;
  width?:    string;
  align?:    'left' | 'center' | 'right';
}

interface TableProps<T> {
  columns:    TableColumn<T>[];
  data:       T[];
  keyField:   keyof T;
  isLoading?: boolean;
  emptyText?: string;
}

export function Table<T>({
  columns,
  data,
  keyField,
  isLoading = false,
  emptyText = '데이터가 없습니다.',
}: TableProps<T>) {
  return (
    <div className={styles.wrapper}>
      <table className={styles.table}>
        <thead className={styles.thead}>
          <tr>
            {columns.map((col) => (
              <th
                key={col.key}
                className={styles.th}
                style={{ width: col.width, textAlign: col.align ?? 'left' }}
              >
                {col.header}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {isLoading ? (
            <tr>
              <td colSpan={columns.length} className={styles.stateCell}>
                <span className={styles.loading}>불러오는 중...</span>
              </td>
            </tr>
          ) : data.length === 0 ? (
            <tr>
              <td colSpan={columns.length} className={styles.stateCell}>
                <span className={styles.empty}>{emptyText}</span>
              </td>
            </tr>
          ) : (
            data.map((row, index) => (
              <tr key={String(row[keyField])} className={styles.tr}>
                {columns.map((col) => (
                  <td
                    key={col.key}
                    className={styles.td}
                    style={{ textAlign: col.align ?? 'left' }}
                  >
                    {col.render
                      ? col.render(row, index)
                      : String(row[col.key as keyof T] ?? '')}
                  </td>
                ))}
              </tr>
            ))
          )}
        </tbody>
      </table>
    </div>
  );
}
