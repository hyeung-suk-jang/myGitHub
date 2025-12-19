#!/bin/bash

################################################################################
# Thread Stuck 체크 스크립트
# WAS 프로세스의 Thread 상태를 분석하여 Stuck Thread 감지
################################################################################

check_thread_stuck() {
    local process_type=$1  # webserver or was
    local process_name=$2  # nginx, tomcat, jeus, etc.
    local process_home=$3
    local process_pid=$4

    local thread_dump_file=""
    local stuck_count=0
    local high_cpu_threads=()

    echo "=== Thread Stuck 체크: $process_name ==="

    # Java 기반 WAS만 체크 (Nginx, Apache는 제외)
    if [[ "$process_name" =~ ^(nginx|apache|webtob|ohs)$ ]]; then
        echo "웹서버는 Thread Stuck 체크를 지원하지 않습니다."
        return 0
    fi

    if [ -z "$process_pid" ]; then
        echo "경고: 프로세스 PID가 없습니다."
        return 1
    fi

    # PID 리스트에서 첫 번째 PID 사용
    local main_pid=$(echo $process_pid | cut -d',' -f1)

    if [ ! -d "/proc/$main_pid" ]; then
        echo "경고: PID $main_pid 프로세스가 존재하지 않습니다."
        return 1
    fi

    # Thread Dump 생성
    thread_dump_file="/tmp/thread_dump_${process_name}_${main_pid}_$(date +%Y%m%d_%H%M%S).txt"

    echo "Thread dump 생성 중... (PID: $main_pid)"

    # jstack을 사용하여 thread dump 생성
    if command -v jstack > /dev/null 2>&1; then
        jstack -l $main_pid > "$thread_dump_file" 2>&1

        if [ $? -ne 0 ]; then
            echo "경고: jstack 실행 실패. 권한을 확인하세요."
            # 대안으로 kill -3 사용
            kill -3 $main_pid 2>/dev/null
            sleep 2
            # 로그에서 thread dump 찾기 (Tomcat의 경우)
            if [ -n "$process_home" ] && [ -d "$process_home/logs" ]; then
                local latest_log=$(ls -t $process_home/logs/catalina.out 2>/dev/null | head -1)
                if [ -n "$latest_log" ]; then
                    tail -5000 "$latest_log" > "$thread_dump_file"
                fi
            fi
        fi
    else
        echo "경고: jstack 명령을 찾을 수 없습니다."
        return 1
    fi

    # Thread Dump 분석
    if [ -f "$thread_dump_file" ] && [ -s "$thread_dump_file" ]; then
        # BLOCKED 상태의 스레드 수 확인
        local blocked_threads=$(grep -c "java.lang.Thread.State: BLOCKED" "$thread_dump_file" 2>/dev/null || echo "0")

        # WAITING 상태의 스레드 수 확인
        local waiting_threads=$(grep -c "java.lang.Thread.State: WAITING" "$thread_dump_file" 2>/dev/null || echo "0")

        # TIMED_WAITING 상태의 스레드 수 확인
        local timed_waiting_threads=$(grep -c "java.lang.Thread.State: TIMED_WAITING" "$thread_dump_file" 2>/dev/null || echo "0")

        # RUNNABLE 상태의 스레드 수 확인
        local runnable_threads=$(grep -c "java.lang.Thread.State: RUNNABLE" "$thread_dump_file" 2>/dev/null || echo "0")

        # 총 스레드 수
        local total_threads=$(grep -c "java.lang.Thread.State:" "$thread_dump_file" 2>/dev/null || echo "0")

        echo "총 스레드 수: $total_threads"
        echo "- RUNNABLE: $runnable_threads"
        echo "- BLOCKED: $blocked_threads"
        echo "- WAITING: $waiting_threads"
        echo "- TIMED_WAITING: $timed_waiting_threads"

        # BLOCKED 스레드가 많으면 경고
        if [ $blocked_threads -gt 10 ]; then
            echo "경고: BLOCKED 스레드가 $blocked_threads 개 발견되었습니다."
            stuck_count=$blocked_threads

            # BLOCKED 스레드 상세 정보 출력
            echo -e "\n주요 BLOCKED 스레드:"
            grep -B 5 "java.lang.Thread.State: BLOCKED" "$thread_dump_file" | head -50
        fi

        # Deadlock 체크
        if grep -q "Found one Java-level deadlock" "$thread_dump_file" 2>/dev/null; then
            echo "치명적: Deadlock이 발견되었습니다!"
            grep -A 20 "Found one Java-level deadlock" "$thread_dump_file"
            stuck_count=$((stuck_count + 100))
        fi
    else
        echo "경고: Thread dump 파일이 생성되지 않았거나 비어있습니다."
    fi

    # CPU 사용률이 높은 스레드 확인
    echo -e "\nCPU 사용률이 높은 스레드 (Top 5):"
    if command -v top > /dev/null 2>&1; then
        top -H -b -n 1 -p $main_pid 2>/dev/null | head -20 | tail -10
    fi

    # 결과 저장
    echo -e "\nThread dump 파일: $thread_dump_file"

    if [ $stuck_count -gt 0 ]; then
        echo "결과: Thread Stuck 또는 Deadlock 발견 ($stuck_count)"
        return 1
    else
        echo "결과: 정상 (심각한 Thread 문제 없음)"
        return 0
    fi
}

# 스크립트가 직접 실행될 때
if [ "${BASH_SOURCE[0]}" == "${0}" ]; then
    if [ $# -lt 2 ]; then
        echo "Usage: $0 <process_type> <process_name> [process_home] [process_pid]"
        echo "Example: $0 was tomcat /opt/tomcat 12345"
        exit 1
    fi

    check_thread_stuck "$@"
fi
