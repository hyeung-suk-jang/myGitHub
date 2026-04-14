import styles from './Header.module.scss';
import { useAppDispatch, useAppSelector } from '@/store';
import { toggleSidebar, setTheme } from '@/store/modules/ui.store';

export function Header() {
  const dispatch = useAppDispatch();
  const theme    = useAppSelector((state) => state.ui.theme);

  const handleToggleTheme = () => {
    dispatch(setTheme(theme === 'light' ? 'dark' : 'light'));
  };

  return (
    <header className={styles.header}>
      <div className={styles.left}>
        <button
          className={styles.menuButton}
          onClick={() => dispatch(toggleSidebar())}
          aria-label="사이드바 토글"
        >
          ☰
        </button>
      </div>
      <div className={styles.right}>
        <button
          className={styles.iconButton}
          onClick={handleToggleTheme}
          aria-label="테마 변경"
        >
          {theme === 'light' ? '🌙' : '☀️'}
        </button>
      </div>
    </header>
  );
}
