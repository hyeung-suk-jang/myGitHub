// ============================================================
// Error Message Map
// ============================================================

import { ERROR_CODE } from './errorCode';
import { ERROR_MESSAGE } from '@/constants/message';

export const HTTP_ERROR_MESSAGE: Record<number, string> = {
  [ERROR_CODE.BAD_REQUEST]:   '잘못된 요청입니다.',
  [ERROR_CODE.UNAUTHORIZED]:  ERROR_MESSAGE.UNAUTHORIZED,
  [ERROR_CODE.FORBIDDEN]:     ERROR_MESSAGE.FORBIDDEN,
  [ERROR_CODE.NOT_FOUND]:     ERROR_MESSAGE.NOT_FOUND,
  [ERROR_CODE.CONFLICT]:      '이미 존재하는 데이터입니다.',
  [ERROR_CODE.UNPROCESSABLE]: '입력값을 확인해 주세요.',
  [ERROR_CODE.SERVER_ERROR]:  ERROR_MESSAGE.SERVER,
  [ERROR_CODE.BAD_GATEWAY]:   '게이트웨이 오류가 발생했습니다.',
  [ERROR_CODE.UNAVAILABLE]:   '서비스를 일시적으로 사용할 수 없습니다.',
};

export function getErrorMessage(status: number): string {
  return HTTP_ERROR_MESSAGE[status] ?? ERROR_MESSAGE.UNKNOWN;
}
