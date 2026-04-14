// ============================================================
// Error Codes
// ============================================================

export const ERROR_CODE = {
  // HTTP Status
  BAD_REQUEST:   400,
  UNAUTHORIZED:  401,
  FORBIDDEN:     403,
  NOT_FOUND:     404,
  CONFLICT:      409,
  UNPROCESSABLE: 422,
  SERVER_ERROR:  500,
  BAD_GATEWAY:   502,
  UNAVAILABLE:   503,

  // Business Error Codes
  TOKEN_EXPIRED:    'TOKEN_EXPIRED',
  TOKEN_INVALID:    'TOKEN_INVALID',
  USER_NOT_FOUND:   'USER_NOT_FOUND',
  DUPLICATE_EMAIL:  'DUPLICATE_EMAIL',
  INVALID_PASSWORD: 'INVALID_PASSWORD',
} as const;

export type HttpStatusCode = typeof ERROR_CODE[
  'BAD_REQUEST' | 'UNAUTHORIZED' | 'FORBIDDEN' | 'NOT_FOUND' |
  'CONFLICT' | 'UNPROCESSABLE' | 'SERVER_ERROR' | 'BAD_GATEWAY' | 'UNAVAILABLE'
];
