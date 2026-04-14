// ============================================================
// Validator Functions
// ============================================================

import { REGEX } from './regex';
import { VALIDATION_MESSAGE } from '@/constants/message';

export function isRequired(value: string | null | undefined): string | null {
  if (!value || value.trim() === '') {
    return VALIDATION_MESSAGE.REQUIRED;
  }
  return null;
}

export function isEmail(value: string): string | null {
  if (!REGEX.EMAIL.test(value)) {
    return VALIDATION_MESSAGE.EMAIL_FORMAT;
  }
  return null;
}

export function isPassword(value: string): string | null {
  if (value.length < 8) {
    return VALIDATION_MESSAGE.PASSWORD_MIN;
  }
  if (!REGEX.PASSWORD.test(value)) {
    return VALIDATION_MESSAGE.PASSWORD_FORMAT;
  }
  return null;
}

export function hasMinLength(value: string, min: number): string | null {
  if (value.length < min) {
    return VALIDATION_MESSAGE.MIN_LENGTH(min);
  }
  return null;
}

export function hasMaxLength(value: string, max: number): string | null {
  if (value.length > max) {
    return VALIDATION_MESSAGE.MAX_LENGTH(max);
  }
  return null;
}

export type ValidatorFn = (value: string) => string | null;

export function composeValidators(...validators: ValidatorFn[]) {
  return (value: string): string | null => {
    for (const validator of validators) {
      const error = validator(value);
      if (error) return error;
    }
    return null;
  };
}
