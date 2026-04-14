import { NavLink } from 'react-router-dom';
import styles from './Sidebar.module.scss';
import { useAppSelector } from '@/store';
import { ROUTE } from '@/constants/route';

const navItems = [
  { label: '대시보드', to: ROUTE.DASHBOARD },
  { label: '분석',     to: ROUTE.ANALYTICS },
  { label: '사용자',   to: ROUTE.USER_LIST  },
] as const;

export function Sidebar() {
  const isOpen = useAppSelector((state) => state.ui.isSidebarOpen);

  return (
    <aside className={[styles.sidebar, isOpen ? styles.open : styles.closed].join(' ')}>
      <div className={styles.logo}>
        <span className={styles.logoText}>React App</span>
      </div>
      <nav className={styles.nav}>
        {navItems.map((item) => (
          <NavLink
            key={item.to}
            to={item.to}
            className={({ isActive }) =>
              [styles.navItem, isActive ? styles.active : ''].filter(Boolean).join(' ')
            }
          >
            {item.label}
          </NavLink>
        ))}
      </nav>
    </aside>
  );
}
