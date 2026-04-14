import type { ReactNode } from 'react';
import styles from './FormControl.module.scss';

interface FormControlProps {
  label?:      string;
  error?:      string;
  hint?:       string;
  isRequired?: boolean;
  htmlFor?:    string;
  children:    ReactNode;
}

export function FormControl({
  label,
  error,
  hint,
  isRequired,
  htmlFor,
  children,
}: FormControlProps) {
  return (
    <div className={styles.formControl}>
      {label && (
        <label htmlFor={htmlFor} className={styles.label}>
          {label}
          {isRequired && <span className={styles.required} aria-hidden="true"> *</span>}
        </label>
      )}
      {children}
      {error && (
        <p className={styles.error} role="alert">
          {error}
        </p>
      )}
      {!error && hint && <p className={styles.hint}>{hint}</p>}
    </div>
  );
}
