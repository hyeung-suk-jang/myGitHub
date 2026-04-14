import styles from './Footer.module.scss';

export function Footer() {
  const year = new Date().getFullYear();

  return (
    <footer className={styles.footer}>
      <p className={styles.copyright}>© {year} React Frontend Template. All rights reserved.</p>
    </footer>
  );
}
