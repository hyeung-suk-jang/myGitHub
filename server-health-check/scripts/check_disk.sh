#!/bin/bash

################################################################################
# 디스크 용량 체크 스크립트
# 시스템 전체 디스크 사용률 및 특정 경로의 디스크 사용률 체크
################################################################################

check_disk_usage() {
    local process_home=$1
    local threshold=${2:-80}  # 기본 임계값 80%

    echo "=== 디스크 용량 체크 ==="
    echo "경고 임계값: ${threshold}%"
    echo ""

    local critical_count=0
    local warning_count=0

    # 전체 디스크 사용률 확인
    echo "[ 전체 디스크 사용 현황 ]"
    df -h | grep -v "tmpfs\|devtmpfs\|Filesystem" | while read line; do
        echo "$line"

        # 사용률 추출 (%)
        local usage=$(echo "$line" | awk '{print $5}' | sed 's/%//')
        local mount_point=$(echo "$line" | awk '{print $6}')

        if [ -n "$usage" ] && [ "$usage" -ge $threshold ]; then
            echo "  ⚠ 경고: $mount_point 사용률 ${usage}% (임계값: ${threshold}%)"
            warning_count=$((warning_count + 1))

            if [ "$usage" -ge 90 ]; then
                echo "  ⛔ 치명적: $mount_point 사용률 ${usage}% (매우 위험)"
                critical_count=$((critical_count + 1))
            fi
        fi
    done

    echo ""

    # 프로세스 홈 디렉토리 디스크 사용률 확인
    if [ -n "$process_home" ] && [ -d "$process_home" ]; then
        echo "[ 프로세스 홈 디렉토리: $process_home ]"

        local mount_point=$(df "$process_home" | tail -1 | awk '{print $6}')
        local usage=$(df "$process_home" | tail -1 | awk '{print $5}' | sed 's/%//')
        local available=$(df -h "$process_home" | tail -1 | awk '{print $4}')

        echo "마운트 포인트: $mount_point"
        echo "사용률: ${usage}%"
        echo "남은 용량: $available"

        if [ "$usage" -ge $threshold ]; then
            echo "⚠ 경고: 사용률 ${usage}% (임계값: ${threshold}%)"
            warning_count=$((warning_count + 1))
        fi

        # 홈 디렉토리 크기 확인
        echo -e "\n홈 디렉토리 크기:"
        du -sh "$process_home" 2>/dev/null

        # 하위 디렉토리 중 큰 디렉토리 확인 (Top 5)
        echo -e "\n대용량 하위 디렉토리 (Top 5):"
        du -sh "$process_home"/* 2>/dev/null | sort -rh | head -5
    fi

    echo ""

    # 로그 디렉토리 확인
    local log_dirs=("/var/log" "$process_home/logs" "$process_home/log")

    for log_dir in "${log_dirs[@]}"; do
        if [ -d "$log_dir" ]; then
            echo "[ 로그 디렉토리: $log_dir ]"
            local log_size=$(du -sh "$log_dir" 2>/dev/null | awk '{print $1}')
            echo "전체 크기: $log_size"

            # 큰 로그 파일 확인 (100MB 이상)
            echo "대용량 로그 파일 (100MB 이상):"
            find "$log_dir" -type f -size +100M -exec ls -lh {} \; 2>/dev/null | awk '{print $5, $9}' | head -10

            # 30일 이상 된 로그 파일 확인
            local old_logs=$(find "$log_dir" -type f -mtime +30 2>/dev/null | wc -l)
            if [ $old_logs -gt 0 ]; then
                echo "30일 이상 된 로그 파일: $old_logs 개"
            fi

            echo ""
        fi
    done

    # Inode 사용률 확인
    echo "[ Inode 사용 현황 ]"
    df -i | grep -v "tmpfs\|devtmpfs\|Filesystem" | while read line; do
        local inode_usage=$(echo "$line" | awk '{print $5}' | sed 's/%//')
        local mount_point=$(echo "$line" | awk '{print $6}')

        if [ -n "$inode_usage" ] && [ "$inode_usage" -ge 80 ]; then
            echo "⚠ 경고: $mount_point Inode 사용률 ${inode_usage}%"
            echo "$line"
            warning_count=$((warning_count + 1))
        fi
    done

    echo ""

    # 결과 요약
    echo "=== 체크 결과 ==="
    if [ $critical_count -gt 0 ]; then
        echo "⛔ 치명적: $critical_count 개의 파티션이 90% 이상 사용 중"
        return 2
    elif [ $warning_count -gt 0 ]; then
        echo "⚠ 경고: $warning_count 개의 파티션이 임계값 초과"
        return 1
    else
        echo "✓ 정상: 모든 디스크 사용률이 정상 범위 내"
        return 0
    fi
}

# 스크립트가 직접 실행될 때
if [ "${BASH_SOURCE[0]}" == "${0}" ]; then
    check_disk_usage "$@"
fi
