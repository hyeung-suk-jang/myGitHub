import type { InputHTMLAttributes } from 'react';
import styles from './Checkbox.module.scss';

interface CheckboxProps extends Omit<InputHTMLAttributes<HTMLInputElement>, 'type'> {
  label?:  string;
  error?:  string;
}

export function Checkbox({ label, error, id, className, ...props }: CheckboxProps) {
  const checkboxId = id ?? `checkbox-${Math.random().toString(36).slice(2)}`;

  return (
    <div className={styles.wrapper}>
      <label htmlFor={checkboxId} className={styles.label}>
        <input
          type="checkbox"
          id={checkboxId}
          className={[styles.checkbox, className ?? ''].filter(Boolean).join(' ')}
          {...props}
        />
        {label && <span className={styles.labelText}>{label}</span>}
      </label>
      {error && (
        <p className={styles.error} role="alert">
          {error}
        </p>
      )}
    </div>
  );
}
