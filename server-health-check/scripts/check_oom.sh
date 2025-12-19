#!/bin/bash

################################################################################
# OutOfMemory 오류 체크 스크립트
# WAS 및 웹서버의 로그 파일에서 OOM 오류를 검색
################################################################################

check_oom_errors() {
    local process_type=$1  # webserver or was
    local process_name=$2  # nginx, tomcat, jeus, etc.
    local process_home=$3
    local process_pid=$4

    local log_paths=()
    local oom_count=0
    local oom_details=""

    # 로그 파일 경로 설정
    case $process_name in
        "nginx")
            log_paths+=("/var/log/nginx/error.log")
            [ -n "$process_home" ] && log_paths+=("$process_home/logs/error.log")
            ;;
        "apache")
            log_paths+=("/var/log/httpd/error_log")
            log_paths+=("/var/log/apache2/error.log")
            [ -n "$process_home" ] && log_paths+=("$process_home/logs/error_log")
            ;;
        "webtob")
            [ -n "$process_home" ] && log_paths+=("$process_home/log/error.log")
            [ -n "$process_home" ] && log_paths+=("$process_home/log/webtoberror.log")
            ;;
        "ohs")
            [ -n "$process_home" ] && log_paths+=("$process_home/logs/error_log")
            ;;
        "tomcat")
            [ -n "$process_home" ] && log_paths+=("$process_home/logs/catalina.out")
            [ -n "$process_home" ] && log_paths+=("$process_home/logs/catalina.log")
            ;;
        "jeus")
            [ -n "$process_home" ] && log_paths+=("$process_home/logs/JeusServer.log")
            [ -n "$process_home" ] && log_paths+=("$process_home/logs/JeusServer.*.log")
            ;;
        "weblogic")
            [ -n "$process_home" ] && log_paths+=("$process_home/servers/*/logs/*.log")
            [ -n "$process_home" ] && log_paths+=("$process_home/servers/*/logs/*.out")
            ;;
        "jboss-eap")
            [ -n "$process_home" ] && log_paths+=("$process_home/standalone/log/server.log")
            [ -n "$process_home" ] && log_paths+=("$process_home/domain/servers/*/log/server.log")
            ;;
    esac

    # 각 로그 파일에서 OOM 오류 검색
    for log_path in "${log_paths[@]}"; do
        # 와일드카드 확장
        for log_file in $log_path; do
            if [ -f "$log_file" ] && [ -r "$log_file" ]; then
                # 최근 24시간 내 OOM 오류 검색
                local recent_oom=$(find "$log_file" -mtime -1 -type f 2>/dev/null)

                if [ -n "$recent_oom" ]; then
                    # OOM 패턴 검색
                    local oom_lines=$(grep -i "OutOfMemoryError\|java.lang.OutOfMemoryError\|OOM\|Out of memory" "$log_file" 2>/dev/null | tail -10)

                    if [ -n "$oom_lines" ]; then
                        local count=$(echo "$oom_lines" | wc -l)
                        oom_count=$((oom_count + count))
                        oom_details+="[$log_file] 발견: $count 건\n"
                        oom_details+="$(echo "$oom_lines" | head -5)\n"
                        oom_details+="---\n"
                    fi
                fi
            fi
        done
    done

    # 시스템 메모리 상태 확인
    local mem_total=$(free -m | awk 'NR==2{print $2}')
    local mem_used=$(free -m | awk 'NR==2{print $3}')
    local mem_free=$(free -m | awk 'NR==2{print $4}')
    local mem_usage_percent=$(awk "BEGIN {printf \"%.2f\", ($mem_used/$mem_total)*100}")

    # 프로세스별 메모리 사용량 확인
    local process_mem=""
    if [ -n "$process_pid" ]; then
        for pid in ${process_pid//,/ }; do
            if [ -d "/proc/$pid" ]; then
                local pid_mem=$(ps -p $pid -o rss= 2>/dev/null | awk '{printf "%.2f", $1/1024}')
                process_mem+="PID $pid: ${pid_mem}MB | "
            fi
        done
    fi

    # 결과 출력
    echo "=== OutOfMemory 체크 결과: $process_name ==="
    echo "OOM 오류 발견: $oom_count 건"
    echo "시스템 메모리: ${mem_used}MB / ${mem_total}MB (사용률: ${mem_usage_percent}%)"
    echo "프로세스 메모리: $process_mem"

    if [ $oom_count -gt 0 ]; then
        echo -e "\n상세 내용:"
        echo -e "$oom_details"
        return 1
    else
        echo "정상: OOM 오류 없음"
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

    check_oom_errors "$@"
fi
