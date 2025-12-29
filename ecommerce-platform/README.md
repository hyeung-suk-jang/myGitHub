# 자사 쇼핑몰 플랫폼

스마트 스토어 등 플랫폼 종속 쇼핑몰을 운영하는 분들을 위한 독립적인 자사 쇼핑몰 솔루션입니다.

## 주요 기능

### 고객용 기능
- 회원가입 및 로그인 (JWT 인증)
- 상품 목록 조회 및 검색
- 상품 상세 정보 확인
- 장바구니 기능
- 주문 및 결제
- 주문 내역 조회
- 배송지 관리
- 상품 리뷰 작성

### 관리자 기능
- 대시보드 (통계 및 현황)
- 상품 관리 (등록, 수정, 삭제)
- 카테고리 관리
- 주문 관리 및 상태 변경
- 재고 관리
- 매출 리포트

## 기술 스택

### Backend
- Node.js + Express
- TypeScript
- Prisma (ORM)
- SQLite (개발) / PostgreSQL (프로덕션)
- JWT 인증
- bcryptjs (비밀번호 암호화)
- Multer (파일 업로드)

### Frontend
- React 18
- TypeScript
- Vite
- React Router v6
- Zustand (상태 관리)
- Tailwind CSS
- Axios

## 프로젝트 구조

```
ecommerce-platform/
├── backend/                 # 백엔드 API 서버
│   ├── prisma/             # 데이터베이스 스키마
│   │   └── schema.prisma
│   ├── src/
│   │   ├── config/         # 설정 파일
│   │   ├── controllers/    # API 컨트롤러
│   │   ├── middleware/     # 미들웨어
│   │   ├── routes/         # API 라우트
│   │   ├── utils/          # 유틸리티 함수
│   │   ├── app.ts          # Express 앱 설정
│   │   └── server.ts       # 서버 진입점
│   ├── package.json
│   └── tsconfig.json
│
└── frontend/                # 프론트엔드 웹 애플리케이션
    ├── src/
    │   ├── components/     # React 컴포넌트
    │   ├── pages/          # 페이지 컴포넌트
    │   ├── store/          # 상태 관리 (Zustand)
    │   ├── lib/            # API 클라이언트
    │   ├── types/          # TypeScript 타입
    │   ├── App.tsx
    │   └── main.tsx
    ├── package.json
    └── vite.config.ts
```

## 설치 및 실행

### 사전 요구사항
- Node.js 18 이상
- npm 또는 yarn

### 1. 백엔드 설정

```bash
# 백엔드 디렉토리로 이동
cd ecommerce-platform/backend

# 의존성 설치
npm install

# 환경 변수 설정
cp .env.example .env
# .env 파일을 열어 필요한 값들을 설정하세요

# 데이터베이스 초기화
npm run db:push

# Prisma Client 생성
npm run db:generate

# 개발 서버 실행
npm run dev
```

백엔드 서버가 `http://localhost:5000`에서 실행됩니다.

### 2. 프론트엔드 설정

```bash
# 새 터미널을 열어 프론트엔드 디렉토리로 이동
cd ecommerce-platform/frontend

# 의존성 설치
npm install

# 개발 서버 실행
npm run dev
```

프론트엔드 애플리케이션이 `http://localhost:3000`에서 실행됩니다.

## API 엔드포인트

### 인증 API
- `POST /api/auth/register` - 회원가입
- `POST /api/auth/login` - 로그인
- `GET /api/auth/profile` - 프로필 조회
- `PUT /api/auth/profile` - 프로필 수정

### 상품 API
- `GET /api/products` - 상품 목록 조회
- `GET /api/products/:id` - 상품 상세 조회
- `POST /api/products` - 상품 등록 (관리자)
- `PUT /api/products/:id` - 상품 수정 (관리자)
- `DELETE /api/products/:id` - 상품 삭제 (관리자)

### 카테고리 API
- `GET /api/categories` - 카테고리 목록 조회
- `POST /api/categories` - 카테고리 생성 (관리자)
- `PUT /api/categories/:id` - 카테고리 수정 (관리자)
- `DELETE /api/categories/:id` - 카테고리 삭제 (관리자)

### 장바구니 API
- `GET /api/cart` - 장바구니 조회
- `POST /api/cart` - 장바구니에 상품 추가
- `PUT /api/cart/:id` - 장바구니 항목 수량 변경
- `DELETE /api/cart/:id` - 장바구니 항목 삭제

### 주문 API
- `GET /api/orders` - 주문 목록 조회
- `GET /api/orders/:id` - 주문 상세 조회
- `POST /api/orders` - 주문 생성
- `POST /api/orders/:id/cancel` - 주문 취소
- `PUT /api/orders/:id/status` - 주문 상태 변경 (관리자)

### 관리자 API
- `GET /api/admin/dashboard` - 대시보드 통계
- `GET /api/admin/sales-report` - 매출 리포트

## 데이터베이스 스키마

주요 모델:
- **User**: 사용자 정보
- **Category**: 상품 카테고리
- **Product**: 상품 정보
- **CartItem**: 장바구니 항목
- **Order**: 주문 정보
- **OrderItem**: 주문 항목
- **Address**: 배송지 정보
- **Review**: 상품 리뷰

## 환경 변수

### Backend (.env)
```
DATABASE_URL=file:./dev.db
JWT_SECRET=your-secret-key
JWT_EXPIRE=7d
PORT=5000
NODE_ENV=development
UPLOAD_DIR=uploads
MAX_FILE_SIZE=5242880
```

## 배포

### 백엔드 배포
1. PostgreSQL 데이터베이스 준비
2. `.env` 파일에서 `DATABASE_URL`을 PostgreSQL 연결 문자열로 변경
3. 빌드: `npm run build`
4. 실행: `npm start`

### 프론트엔드 배포
1. 빌드: `npm run build`
2. `dist` 폴더를 정적 파일 서버나 CDN에 배포

## 주요 기능 설명

### 1. 인증 시스템
- JWT 기반 인증
- 비밀번호 bcrypt 암호화
- 사용자 역할 기반 권한 관리 (USER, ADMIN)

### 2. 상품 관리
- 이미지 업로드 지원 (최대 5개)
- 카테고리별 상품 분류
- 할인가 설정 가능
- 재고 관리

### 3. 장바구니
- 로그인 사용자만 이용 가능
- 실시간 재고 확인
- 수량 조절 및 삭제

### 4. 주문 시스템
- 배송지 관리
- 주문 상태 추적
- 주문 취소 기능

### 5. 관리자 기능
- 실시간 대시보드
- 주문 및 재고 관리
- 매출 통계 및 리포트

## 보안 고려사항

- JWT 토큰 인증
- 비밀번호 암호화
- SQL Injection 방지 (Prisma ORM)
- XSS 방지
- CORS 설정
- 파일 업로드 제한 및 검증

## 개발 팁

### 관리자 계정 생성
데이터베이스에서 직접 사용자의 role을 'ADMIN'으로 변경하거나,
회원가입 후 Prisma Studio를 사용하여 role을 변경할 수 있습니다:

```bash
cd backend
npm run db:studio
```

## 라이센스

MIT

## 기여

이슈 및 풀 리퀘스트는 언제든 환영합니다.

## 문의

프로젝트에 대한 문의사항이 있으시면 이슈를 등록해주세요.
