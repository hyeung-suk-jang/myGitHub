# 서버 헬스 체크 시스템

폴스타(Polestar)를 통해 미들웨어가 설치된 모든 서버에 대해 일괄 점검을 수행하는 자동화 스크립트입니다.

## 주요 기능

### 1. 미들웨어 자동 감지
- **웹서버**: Nginx, Apache, WebtoB, Oracle HTTP Server (OHS)
- **WAS**: Tomcat, JEUS, WebLogic, JBoss EAP

### 2. 헬스 체크 항목
- **OutOfMemory 오류 검사**
  - 로그 파일에서 OOM 오류 패턴 검색
  - 최근 24시간 내 발생한 오류 추적
  - 시스템 및 프로세스 메모리 사용률 확인

- **Thread Stuck 검사**
  - Java 프로세스의 Thread Dump 생성 및 분석
  - BLOCKED, WAITING 상태 스레드 감지
  - Deadlock 탐지
  - CPU 사용률이 높은 스레드 식별

- **디스크 용량 검사**
  - 전체 파티션 사용률 확인
  - 프로세스 홈 디렉토리 용량 분석
  - 로그 디렉토리 대용량 파일 식별
  - Inode 사용률 체크

## 디렉토리 구조

```
server-health-check/
├── health_check.sh           # 메인 실행 스크립트
├── config/
│   └── servers.conf          # 서버 목록 설정 파일
├── scripts/
│   ├── detect_webserver.sh   # 웹서버 감지 모듈
│   ├── detect_was.sh         # WAS 감지 모듈
│   ├── check_oom.sh          # OutOfMemory 체크 모듈
│   ├── check_thread_stuck.sh # Thread Stuck 체크 모듈
│   └── check_disk.sh         # 디스크 용량 체크 모듈
├── logs/                     # 실행 로그 및 리포트 저장
└── README.md                 # 이 문서
```

## 설치 및 설정

### 1. 사전 요구사항

- **운영 서버**: Linux (CentOS, Ubuntu, RHEL 등)
- **필수 도구**:
  - SSH (서버 간 통신)
  - Bash 4.0 이상
  - Java (WAS 점검 시 Thread Dump를 위해 필요)
  - jstack (Thread Dump 생성용)

### 2. SSH 키 설정

서버 간 비밀번호 없이 접속하기 위해 SSH 키를 설정합니다.

```bash
# SSH 키 생성 (이미 있다면 생략)
ssh-keygen -t rsa -b 4096

# 대상 서버에 공개키 복사
ssh-copy-id -p [포트] [사용자명]@[서버IP]

# 접속 테스트
ssh -p [포트] [사용자명]@[서버IP]
```

### 3. 서버 목록 설정

`config/servers.conf` 파일을 편집하여 점검할 서버 목록을 설정합니다.

```bash
# 형식: 서버IP|SSH포트|사용자명|설명
192.168.1.100|22|appuser|운영 웹서버 1
192.168.1.101|22|appuser|운영 WAS 서버 1
192.168.1.102|22|appuser|운영 WAS 서버 2
```

**주의사항**:
- 주석은 `#`으로 시작
- 각 필드는 `|` (파이프)로 구분
- SSH 포트는 기본적으로 22번 사용
- 사용자는 대상 서버의 로그 및 프로세스 조회 권한이 필요

## 사용 방법

### 기본 실행

```bash
cd server-health-check
./health_check.sh
```

### 실행 결과

실행이 완료되면 다음 파일들이 생성됩니다:

1. **상세 로그**: `logs/health_check_[타임스탬프].log`
   - 모든 실행 과정 및 결과가 기록됨

2. **요약 리포트**: `logs/health_report_[타임스탬프].txt`
   - 주요 발견 사항 요약

### 출력 예시

```
[INFO] ==========================================
[INFO] 서버 헬스 체크 시작
[INFO] 시간: 2025-12-19 10:30:00
[INFO] ==========================================

[INFO] 서버 헬스 체크 시작: 운영 웹서버 1 (192.168.1.100)
[INFO] 서버 연결 성공
[INFO] 1. 웹서버 감지 중...
[INFO] 웹서버 발견:
[INFO]   - nginx (버전: 1.18.0, PID: 1234)
[INFO]   - OutOfMemory 오류 체크...
[INFO]   - 디스크 용량 체크...

[INFO] 2. WAS 감지 중...
[INFO] WAS 발견:
[INFO]   - tomcat (버전: Apache Tomcat/9.0.50, 홈: /opt/tomcat, PID: 5678)
[INFO]   - OutOfMemory 오류 체크...
[INFO]   - Thread Stuck 체크...
[INFO]   - 디스크 용량 체크...
```

## 개별 모듈 사용

각 모듈은 독립적으로 실행할 수 있습니다.

### 웹서버 감지

```bash
./scripts/detect_webserver.sh
```

### WAS 감지

```bash
./scripts/detect_was.sh
```

### OutOfMemory 체크

```bash
./scripts/check_oom.sh was tomcat /opt/tomcat 5678
```

### Thread Stuck 체크

```bash
./scripts/check_thread_stuck.sh was tomcat /opt/tomcat 5678
```

### 디스크 용량 체크

```bash
./scripts/check_disk.sh /opt/tomcat 80
# 80은 경고 임계값 (기본값: 80%)
```

## 문제 해결

### SSH 접속 실패

**증상**: `서버 연결 실패` 메시지 표시

**해결 방법**:
1. SSH 키가 올바르게 설정되었는지 확인
2. 대상 서버의 SSH 서비스가 실행 중인지 확인
3. 방화벽 설정 확인
4. `servers.conf`의 IP, 포트, 사용자명이 정확한지 확인

```bash
# 수동 접속 테스트
ssh -p 22 appuser@192.168.1.100
```

### 권한 오류

**증상**: `permission denied` 또는 로그 파일을 읽을 수 없음

**해결 방법**:
1. 스크립트 사용자가 대상 서버에서 로그 파일 읽기 권한이 있는지 확인
2. 필요한 경우 sudo 권한 설정
3. 로그 파일 경로 및 권한 확인

```bash
# 로그 파일 권한 확인
ls -la /var/log/tomcat/
```

### jstack 명령을 찾을 수 없음

**증상**: Thread Stuck 체크 시 `jstack 명령을 찾을 수 없습니다` 메시지

**해결 방법**:
1. JDK가 설치되어 있는지 확인 (JRE만으로는 jstack이 없음)
2. PATH 환경변수에 JDK bin 디렉토리 추가

```bash
# jstack 위치 확인
which jstack

# JDK 설치 확인
java -version

# PATH 설정
export PATH=$PATH:/usr/lib/jvm/java-11-openjdk/bin
```

## 주의사항

1. **권한 관리**: 대상 서버에 접속하는 사용자는 최소 권한 원칙을 따라야 합니다.
2. **로그 보관**: 로그 파일은 주기적으로 정리하여 디스크 공간을 관리하세요.
3. **Thread Dump**: Thread dump 생성 시 일시적으로 WAS 성능에 영향을 줄 수 있습니다.
4. **운영 시간**: 가능하면 피크 타임을 피해 실행하는 것을 권장합니다.

## 고급 설정

### Cron을 이용한 주기적 실행

매일 오전 2시에 헬스 체크를 자동으로 실행하도록 설정:

```bash
# crontab 편집
crontab -e

# 다음 라인 추가
0 2 * * * /home/user/server-health-check/health_check.sh >> /home/user/server-health-check/logs/cron.log 2>&1
```

### 임계값 커스터마이징

`check_disk.sh` 스크립트에서 디스크 사용률 임계값을 변경할 수 있습니다:

```bash
# 기본값 80%에서 90%로 변경
./scripts/check_disk.sh /opt/tomcat 90
```

## 라이선스

이 프로젝트는 내부용으로 제작되었습니다.

## 작성자

Polestar Team

## 버전 히스토리

- **v1.0.0** (2025-12-19)
  - 초기 버전 릴리스
  - 웹서버/WAS 자동 감지 기능
  - OOM, Thread Stuck, 디스크 용량 체크 기능
