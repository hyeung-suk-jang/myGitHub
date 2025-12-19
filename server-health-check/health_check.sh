#!/bin/bash

################################################################################
# 서버 헬스 체크 메인 스크립트
# 폴스타를 통해 미들웨어가 설치된 모든 서버에 대해 일괄 점검
# 작성자: Polestar Team
# 버전: 1.0.0
################################################################################

# 스크립트 경로 설정
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
CONFIG_DIR="$SCRIPT_DIR/config"
SCRIPTS_DIR="$SCRIPT_DIR/scripts"
LOG_DIR="$SCRIPT_DIR/logs"

# 설정 파일
SERVER_LIST="$CONFIG_DIR/servers.conf"

# 색상 코드
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 로그 파일
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
MAIN_LOG="$LOG_DIR/health_check_${TIMESTAMP}.log"
REPORT_FILE="$LOG_DIR/health_report_${TIMESTAMP}.txt"

# 디렉토리 생성
mkdir -p "$LOG_DIR"

################################################################################
# 함수 정의
################################################################################

# 로그 출력 함수
log() {
    local level=$1
    shift
    local message="$@"
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')

    case $level in
        INFO)
            echo -e "${GREEN}[INFO]${NC} $message" | tee -a "$MAIN_LOG"
            ;;
        WARN)
            echo -e "${YELLOW}[WARN]${NC} $message" | tee -a "$MAIN_LOG"
            ;;
        ERROR)
            echo -e "${RED}[ERROR]${NC} $message" | tee -a "$MAIN_LOG"
            ;;
        *)
            echo "$message" | tee -a "$MAIN_LOG"
            ;;
    esac
}

# 서버 연결 확인
check_server_connection() {
    local server_ip=$1
    local ssh_port=$2
    local username=$3

    log INFO "서버 연결 확인: $username@$server_ip:$ssh_port"

    # SSH 연결 테스트 (타임아웃 5초)
    ssh -o ConnectTimeout=5 -o StrictHostKeyChecking=no -p $ssh_port $username@$server_ip "exit" 2>/dev/null

    if [ $? -eq 0 ]; then
        log INFO "서버 연결 성공"
        return 0
    else
        log ERROR "서버 연결 실패"
        return 1
    fi
}

# 원격 서버에서 스크립트 실행
execute_remote_script() {
    local server_ip=$1
    local ssh_port=$2
    local username=$3
    local script_path=$4
    shift 4
    local args="$@"

    # 스크립트를 원격 서버로 전송하고 실행
    local script_name=$(basename "$script_path")
    local remote_script="/tmp/$script_name"

    # 스크립트 업로드
    scp -P $ssh_port -o StrictHostKeyChecking=no "$script_path" "$username@$server_ip:$remote_script" >/dev/null 2>&1

    if [ $? -ne 0 ]; then
        log ERROR "스크립트 업로드 실패: $script_name"
        return 1
    fi

    # 스크립트 실행
    ssh -p $ssh_port -o StrictHostKeyChecking=no $username@$server_ip "chmod +x $remote_script && $remote_script $args" 2>&1

    # 원격 스크립트 삭제
    ssh -p $ssh_port -o StrictHostKeyChecking=no $username@$server_ip "rm -f $remote_script" >/dev/null 2>&1

    return 0
}

# 서버 헬스 체크 수행
perform_health_check() {
    local server_ip=$1
    local ssh_port=$2
    local username=$3
    local description=$4

    echo "" | tee -a "$MAIN_LOG"
    echo "================================================================================" | tee -a "$MAIN_LOG"
    log INFO "서버 헬스 체크 시작: $description ($server_ip)"
    echo "================================================================================" | tee -a "$MAIN_LOG"

    # 서버 연결 확인
    if ! check_server_connection "$server_ip" "$ssh_port" "$username"; then
        log ERROR "서버 접속 실패로 헬스 체크를 건너뜁니다."
        return 1
    fi

    # 웹서버 감지
    log INFO "1. 웹서버 감지 중..."
    local webservers=$(execute_remote_script "$server_ip" "$ssh_port" "$username" "$SCRIPTS_DIR/detect_webserver.sh")

    if [ "$webservers" != "NONE" ] && [ -n "$webservers" ]; then
        log INFO "웹서버 발견:"
        echo "$webservers" | while IFS='|' read -r name version pid; do
            log INFO "  - $name (버전: $version, PID: $pid)"

            # 웹서버별 헬스 체크
            echo "" | tee -a "$MAIN_LOG"
            log INFO "웹서버 헬스 체크: $name"

            # OOM 체크
            log INFO "  - OutOfMemory 오류 체크..."
            execute_remote_script "$server_ip" "$ssh_port" "$username" "$SCRIPTS_DIR/check_oom.sh" "webserver" "$name" "" "$pid"

            # 디스크 체크
            log INFO "  - 디스크 용량 체크..."
            execute_remote_script "$server_ip" "$ssh_port" "$username" "$SCRIPTS_DIR/check_disk.sh"
        done
    else
        log WARN "활성화된 웹서버를 찾을 수 없습니다."
    fi

    # WAS 감지
    log INFO "2. WAS 감지 중..."
    local was_list=$(execute_remote_script "$server_ip" "$ssh_port" "$username" "$SCRIPTS_DIR/detect_was.sh")

    if [ "$was_list" != "NONE" ] && [ -n "$was_list" ]; then
        log INFO "WAS 발견:"
        echo "$was_list" | while IFS='|' read -r name version home pids; do
            log INFO "  - $name (버전: $version, 홈: $home, PID: $pids)"

            # WAS별 헬스 체크
            echo "" | tee -a "$MAIN_LOG"
            log INFO "WAS 헬스 체크: $name"

            # OOM 체크
            log INFO "  - OutOfMemory 오류 체크..."
            execute_remote_script "$server_ip" "$ssh_port" "$username" "$SCRIPTS_DIR/check_oom.sh" "was" "$name" "$home" "$pids"

            # Thread Stuck 체크
            log INFO "  - Thread Stuck 체크..."
            execute_remote_script "$server_ip" "$ssh_port" "$username" "$SCRIPTS_DIR/check_thread_stuck.sh" "was" "$name" "$home" "$pids"

            # 디스크 체크
            log INFO "  - 디스크 용량 체크..."
            execute_remote_script "$server_ip" "$ssh_port" "$username" "$SCRIPTS_DIR/check_disk.sh" "$home"
        done
    else
        log WARN "활성화된 WAS를 찾을 수 없습니다."
    fi

    log INFO "서버 헬스 체크 완료: $description"
    echo "" | tee -a "$MAIN_LOG"
}

################################################################################
# 메인 실행
################################################################################

main() {
    log INFO "=========================================="
    log INFO "서버 헬스 체크 시작"
    log INFO "시간: $(date '+%Y-%m-%d %H:%M:%S')"
    log INFO "=========================================="

    # 서버 목록 파일 확인
    if [ ! -f "$SERVER_LIST" ]; then
        log ERROR "서버 목록 파일을 찾을 수 없습니다: $SERVER_LIST"
        exit 1
    fi

    # 필수 스크립트 확인
    local required_scripts=(
        "$SCRIPTS_DIR/detect_webserver.sh"
        "$SCRIPTS_DIR/detect_was.sh"
        "$SCRIPTS_DIR/check_oom.sh"
        "$SCRIPTS_DIR/check_thread_stuck.sh"
        "$SCRIPTS_DIR/check_disk.sh"
    )

    for script in "${required_scripts[@]}"; do
        if [ ! -f "$script" ]; then
            log ERROR "필수 스크립트를 찾을 수 없습니다: $script"
            exit 1
        fi
        chmod +x "$script"
    done

    # 서버 목록 읽기 및 처리
    local server_count=0
    local success_count=0
    local fail_count=0

    while IFS='|' read -r server_ip ssh_port username description; do
        # 주석 및 빈 줄 무시
        [[ "$server_ip" =~ ^#.*$ ]] && continue
        [[ -z "$server_ip" ]] && continue

        server_count=$((server_count + 1))

        # 헬스 체크 수행
        if perform_health_check "$server_ip" "$ssh_port" "$username" "$description"; then
            success_count=$((success_count + 1))
        else
            fail_count=$((fail_count + 1))
        fi

    done < "$SERVER_LIST"

    # 최종 결과
    echo "" | tee -a "$MAIN_LOG"
    log INFO "=========================================="
    log INFO "헬스 체크 완료"
    log INFO "총 서버 수: $server_count"
    log INFO "성공: $success_count, 실패: $fail_count"
    log INFO "로그 파일: $MAIN_LOG"
    log INFO "=========================================="

    # 결과 요약 리포트 생성
    generate_report
}

# 결과 리포트 생성
generate_report() {
    cat > "$REPORT_FILE" <<EOF
================================================================================
서버 헬스 체크 리포트
생성 시간: $(date '+%Y-%m-%d %H:%M:%S')
================================================================================

상세 로그: $MAIN_LOG

주요 발견 사항:
$(grep -E "경고|치명적|WARNING|ERROR|CRITICAL" "$MAIN_LOG" | head -20)

================================================================================
EOF

    log INFO "리포트 생성 완료: $REPORT_FILE"
}

# 스크립트 실행
main "$@"
