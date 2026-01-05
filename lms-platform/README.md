# LMS 플랫폼 (Learning Management System)

HTML, CSS, JavaScript, jQuery, Python 기반의 종합 학습 관리 시스템

## 주요 기능

### 1. 시험 관리
- 문제 은행 (CRUD, 분류, 단원, 난이도, 키워드)
- 시험지 생성 (수동 입력, 파일 업로드, 단원별 선택)
- 응시 설정 (기간, 시간 제한, 재응시 여부, 셔플, 합격선)
- 시험 배정 (반/개인 배정)
- 자동/수동 채점
- 성적 리포트 및 실력 추이 관리
- 객관식, 주관식, 선잇기 문제 유형 지원

### 2. 교재 관리
- 교재/강의 노트 업로드
- 과제 PDF, 동영상 링크 등록
- 버전 관리
- 접근 권한 설정 (반/수강생/강사)
- 다운로드/뷰어 기능
- 태그 및 검색 기능

### 3. 학생 관리
- 반/과정 등록
- 수강 신청/배정
- 출결 관리
- 학습 진도 추적
- 성적 및 수료 관리
- 알림/공지 시스템
- 상담 메모
- 계정/권한 관리

### 4. 과정 관리
- 과정 등록 및 관리
- 교재 등록
- 학생 배정
- 로드맵 등록

### 5. 강사 관리
- 강사 프로필 및 계약 정보
- 강의 시간표 및 강의실 배정
- 출결 관리
- 과제/시험 채점 및 피드백
- 정산 리포트 (시간/강의 수 기반)

### 6. 사용자 권한 관리
- 관리자
- 강사
- 시스템 관리자
- 학생
- 조교

## 기술 스택

### 백엔드
- Python 3.x
- Flask (웹 프레임워크)
- SQLite (데이터베이스)

### 프론트엔드
- HTML5
- CSS3
- JavaScript (ES6+)
- jQuery

## 프로젝트 구조

```
lms-platform/
├── backend/
│   ├── api/                 # REST API 엔드포인트
│   │   ├── auth.py         # 인증 API
│   │   ├── courses.py      # 과정 관리 API
│   │   ├── exams.py        # 시험 관리 API
│   │   ├── materials.py    # 교재 관리 API
│   │   └── students.py     # 학생 관리 API
│   ├── config/             # 설정 파일
│   │   └── config.py
│   ├── models/             # 데이터 모델
│   ├── utils/              # 유틸리티
│   │   └── database.py
│   ├── app.py              # Flask 애플리케이션
│   └── requirements.txt    # Python 의존성
├── frontend/
│   ├── user/               # 사용자 사이트 (학생/강사)
│   │   ├── login.html      # 로그인
│   │   ├── index.html      # 대시보드
│   │   ├── courses.html    # 과정 목록
│   │   ├── exams.html      # 시험 목록
│   │   ├── materials.html  # 교재 목록
│   │   └── my-profile.html # 내 정보
│   ├── admin/              # 관리자 사이트
│   │   ├── index.html      # 관리자 대시보드
│   │   ├── courses.html    # 과정 관리
│   │   ├── students.html   # 학생 관리
│   │   ├── exams.html      # 시험 관리
│   │   ├── materials.html  # 교재 관리
│   │   └── users.html      # 사용자 관리
│   └── common/             # 공통 리소스
│       ├── css/
│       │   └── style.css   # 공통 스타일
│       └── js/
│           ├── api.js      # API 통신
│           └── utils.js    # 유틸리티 함수
├── database/
│   └── schema.sql          # 데이터베이스 스키마
└── README.md
```

## 설치 및 실행

### 1. Python 의존성 설치

```bash
cd lms-platform/backend
pip install -r requirements.txt
```

### 2. 데이터베이스 초기화 및 서버 실행

```bash
python app.py
```

서버가 시작되면 자동으로 데이터베이스가 초기화되고 샘플 데이터가 삽입됩니다.

### 3. 브라우저에서 접속

- **사용자 사이트**: http://localhost:5000/user
- **관리자 사이트**: http://localhost:5000/admin
- **API 문서**: http://localhost:5000/api

## 기본 계정

### 관리자
- 아이디: `admin`
- 비밀번호: `admin123`

### 강사
- 아이디: `instructor1`
- 비밀번호: `instructor123`

### 학생
- 아이디: `student1`
- 비밀번호: `student123`
- 아이디: `student2`
- 비밀번호: `student123`

## API 엔드포인트

### 인증 (Auth)
- `POST /api/auth/login` - 로그인
- `POST /api/auth/logout` - 로그아웃
- `GET /api/auth/check` - 인증 확인
- `POST /api/auth/register` - 회원가입

### 과정 (Courses)
- `GET /api/courses/` - 과정 목록 조회
- `GET /api/courses/<id>` - 특정 과정 조회
- `POST /api/courses/` - 과정 생성
- `PUT /api/courses/<id>` - 과정 수정
- `DELETE /api/courses/<id>` - 과정 삭제
- `POST /api/courses/<id>/enroll` - 수강 신청

### 시험 (Exams)
- `GET /api/exams/` - 시험 목록 조회
- `GET /api/exams/<id>` - 시험 상세 조회
- `POST /api/exams/` - 시험 생성
- `POST /api/exams/<id>/start` - 시험 시작
- `POST /api/exams/attempts/<id>/submit` - 시험 제출
- `GET /api/exams/attempts/<id>/results` - 시험 결과 조회

### 문제 은행 (Questions)
- `GET /api/exams/questions` - 문제 목록 조회
- `POST /api/exams/questions` - 문제 생성
- `PUT /api/exams/questions/<id>` - 문제 수정
- `DELETE /api/exams/questions/<id>` - 문제 삭제

### 교재 (Materials)
- `GET /api/materials/` - 교재 목록 조회
- `GET /api/materials/<id>` - 교재 상세 조회
- `POST /api/materials/` - 교재 등록
- `PUT /api/materials/<id>` - 교재 수정
- `DELETE /api/materials/<id>` - 교재 삭제
- `POST /api/materials/<id>/progress` - 학습 진도 업데이트

### 학생 (Students)
- `GET /api/students/` - 학생 목록 조회
- `GET /api/students/<id>` - 학생 상세 정보 조회
- `GET /api/students/<id>/attendance` - 출결 기록 조회
- `GET /api/students/<id>/counseling` - 상담 메모 조회
- `POST /api/students/<id>/counseling` - 상담 메모 추가
- `GET /api/students/my-dashboard` - 학생 대시보드

## 데이터베이스 스키마

주요 테이블:
- `users` - 사용자 정보
- `instructor_profiles` - 강사 프로필
- `courses` - 과정 정보
- `classes` - 반 정보
- `enrollments` - 수강 신청
- `materials` - 교재
- `material_permissions` - 교재 접근 권한
- `questions` - 문제 은행
- `exams` - 시험지
- `exam_questions` - 시험지-문제 매핑
- `exam_assignments` - 시험 배정
- `exam_attempts` - 시험 응시
- `student_answers` - 학생 답안
- `attendance` - 출결 기록
- `learning_progress` - 학습 진도
- `announcements` - 공지사항
- `counseling_notes` - 상담 메모
- `instructor_settlements` - 강의 정산
- `assignments` - 과제
- `assignment_submissions` - 과제 제출

## 주요 화면

### 사용자 사이트
1. **로그인** - 사용자 인증
2. **대시보드** - 출결 통계, 수강 과정, 최근 시험 결과, 학습 진도
3. **수강 과정** - 과정 목록 및 수강 신청
4. **시험** - 배정된 시험 목록 및 응시
5. **교재** - 학습 자료 열람 및 다운로드
6. **내 정보** - 프로필, 수강 이력, 시험 성적

### 관리자 사이트
1. **관리자 대시보드** - 전체 통계 및 최근 활동
2. **과정 관리** - 과정 CRUD
3. **학생 관리** - 학생 목록 및 상세 정보
4. **시험 관리** - 시험 생성, 문제 은행 관리
5. **교재 관리** - 교재 등록 및 관리
6. **사용자 관리** - 전체 사용자 관리

## 개발 참고사항

### 보안
- 현재 비밀번호는 평문으로 저장됩니다. 실제 운영 시 bcrypt 등을 사용하여 해싱 필요
- CORS는 개발 환경에서 모두 허용되어 있습니다. 운영 시 제한 필요
- 세션 관리를 강화하여 보안 향상 필요

### 추가 개발 필요 기능
- 파일 업로드 기능 (교재, 과제 제출)
- 실시간 알림 시스템
- 이메일 알림
- 실제 시험 응시 화면
- 채점 대시보드 및 분석 기능
- 오답 노트 기능
- 성적 추이 그래프
- 강사 정산 자동화
- 출결 자동 체크
- 모바일 반응형 개선

## 라이선스

이 프로젝트는 교육 목적으로 개발되었습니다.

## 문의

프로젝트 관련 문의사항이 있으시면 이슈를 등록해주세요.
