import type { SelectHTMLAttributes } from 'react';
import type { SelectOption } from '@/types/common.type';
import styles from './Select.module.scss';

interface SelectProps extends SelectHTMLAttributes<HTMLSelectElement> {
  options:     SelectOption[];
  label?:      string;
  error?:      string;
  placeholder?: string;
  isRequired?:  boolean;
}

export function Select({
  options,
  label,
  error,
  placeholder,
  isRequired,
  id,
  className,
  ...props
}: SelectProps) {
  const selectId = id ?? `select-${Math.random().toString(36).slice(2)}`;

  return (
    <div className={styles.wrapper}>
      {label && (
        <label htmlFor={selectId} className={styles.label}>
          {label}
          {isRequired && <span className={styles.required}> *</span>}
        </label>
      )}
      <select
        id={selectId}
        className={[
          styles.select,
          error ? styles.hasError : '',
          className ?? '',
        ]
          .filter(Boolean)
          .join(' ')}
        aria-invalid={!!error}
        {...props}
      >
        {placeholder && (
          <option value="" disabled>
            {placeholder}
          </option>
        )}
        {options.map((opt) => (
          <option key={String(opt.value)} value={String(opt.value)} disabled={opt.disabled}>
            {opt.label}
          </option>
        ))}
      </select>
      {error && (
        <p className={styles.error} role="alert">
          {error}
        </p>
      )}
    </div>
  );
}
