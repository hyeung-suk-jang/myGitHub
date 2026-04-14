import type { InputHTMLAttributes, ReactNode } from 'react';
import styles from './Input.module.scss';

type InputSize = 'sm' | 'md' | 'lg';

interface InputProps extends InputHTMLAttributes<HTMLInputElement> {
  label?:       string;
  error?:       string;
  hint?:        string;
  inputSize?:   InputSize;
  leftIcon?:    ReactNode;
  rightIcon?:   ReactNode;
  isRequired?:  boolean;
}

export function Input({
  label,
  error,
  hint,
  inputSize = 'md',
  leftIcon,
  rightIcon,
  isRequired,
  className,
  id,
  ...props
}: InputProps) {
  const inputId = id ?? `input-${Math.random().toString(36).slice(2)}`;

  return (
    <div className={styles.wrapper}>
      {label && (
        <label htmlFor={inputId} className={styles.label}>
          {label}
          {isRequired && <span className={styles.required} aria-hidden="true"> *</span>}
        </label>
      )}
      <div className={styles.inputWrapper}>
        {leftIcon && <span className={styles.leftIcon}>{leftIcon}</span>}
        <input
          id={inputId}
          className={[
            styles.input,
            styles[inputSize],
            error    ? styles.hasError  : '',
            leftIcon ? styles.hasLeft   : '',
            rightIcon ? styles.hasRight : '',
            className ?? '',
          ]
            .filter(Boolean)
            .join(' ')}
          aria-invalid={!!error}
          aria-describedby={error ? `${inputId}-error` : hint ? `${inputId}-hint` : undefined}
          {...props}
        />
        {rightIcon && <span className={styles.rightIcon}>{rightIcon}</span>}
      </div>
      {error && (
        <p id={`${inputId}-error`} className={styles.error} role="alert">
          {error}
        </p>
      )}
      {!error && hint && (
        <p id={`${inputId}-hint`} className={styles.hint}>
          {hint}
        </p>
      )}
    </div>
  );
}
