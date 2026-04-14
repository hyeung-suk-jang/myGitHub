// ============================================================
// Message Constants
// ============================================================

export const ERROR_MESSAGE = {
  NETWORK:      '네트워크 오류가 발생했습니다.',
  UNAUTHORIZED: '로그인이 필요합니다.',
  FORBIDDEN:    '접근 권한이 없습니다.',
  NOT_FOUND:    '요청한 데이터를 찾을 수 없습니다.',
  SERVER:       '서버 오류가 발생했습니다.',
  UNKNOWN:      '알 수 없는 오류가 발생했습니다.',
  TIMEOUT:      '요청 시간이 초과되었습니다.',
} as const;

export const SUCCESS_MESSAGE = {
  CREATE: '등록이 완료되었습니다.',
  UPDATE: '수정이 완료되었습니다.',
  DELETE: '삭제가 완료되었습니다.',
  SAVE:   '저장이 완료되었습니다.',
  LOGIN:  '로그인이 완료되었습니다.',
  LOGOUT: '로그아웃 되었습니다.',
} as const;

export const VALIDATION_MESSAGE = {
  REQUIRED:        '필수 입력 항목입니다.',
  EMAIL_FORMAT:    '올바른 이메일 형식이 아닙니다.',
  PASSWORD_MIN:    '비밀번호는 8자 이상이어야 합니다.',
  PASSWORD_FORMAT: '비밀번호는 영문, 숫자, 특수문자를 포함해야 합니다.',
  MAX_LENGTH:      (max: number) => `최대 ${max}자까지 입력 가능합니다.`,
  MIN_LENGTH:      (min: number) => `최소 ${min}자 이상 입력해 주세요.`,
} as const;
