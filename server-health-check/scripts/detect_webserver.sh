#!/bin/bash

################################################################################
# 웹서버 감지 스크립트
# 지원: Nginx, Apache, WebtoB, Oracle HTTP Server (OHS)
################################################################################

detect_webserver() {
    local webservers=()

    # Nginx 감지
    if pgrep -x "nginx" > /dev/null 2>&1; then
        local nginx_version=$(nginx -v 2>&1 | grep -oP 'nginx/\K[0-9.]+')
        local nginx_config=$(nginx -V 2>&1 | grep -oP 'configure arguments.*' | sed 's/configure arguments: //')
        local nginx_pid=$(pgrep -x nginx | head -1)
        webservers+=("nginx|$nginx_version|$nginx_pid")
    fi

    # Apache 감지
    if pgrep -x "httpd" > /dev/null 2>&1 || pgrep -x "apache2" > /dev/null 2>&1; then
        local apache_cmd="httpd"
        if command -v httpd > /dev/null 2>&1; then
            apache_cmd="httpd"
        elif command -v apache2 > /dev/null 2>&1; then
            apache_cmd="apache2"
        fi

        local apache_version=$($apache_cmd -v 2>&1 | grep -oP 'Apache/\K[0-9.]+' | head -1)
        local apache_pid=$(pgrep -x httpd -o 2>/dev/null || pgrep -x apache2 -o 2>/dev/null)
        webservers+=("apache|$apache_version|$apache_pid")
    fi

    # WebtoB 감지
    if pgrep -f "wsboot" > /dev/null 2>&1 || pgrep -f "webtob" > /dev/null 2>&1; then
        local webtob_home=$(ps -ef | grep wsboot | grep -v grep | awk '{for(i=1;i<=NF;i++) if($i ~ /WEBTOBDIR/) print $i}' | cut -d'=' -f2 | head -1)
        local webtob_version="unknown"
        if [ -n "$webtob_home" ] && [ -f "$webtob_home/version.txt" ]; then
            webtob_version=$(cat "$webtob_home/version.txt" 2>/dev/null | head -1)
        fi
        local webtob_pid=$(pgrep -f wsboot | head -1)
        webservers+=("webtob|$webtob_version|$webtob_pid")
    fi

    # Oracle HTTP Server (OHS) 감지
    if pgrep -f "ohs" > /dev/null 2>&1; then
        local ohs_pid=$(pgrep -f "ohs" | head -1)
        local ohs_home=$(ps -ef | grep ohs | grep -v grep | awk '{for(i=1;i<=NF;i++) if($i ~ /OHS/) print $i}' | head -1)
        local ohs_version="unknown"
        webservers+=("ohs|$ohs_version|$ohs_pid")
    fi

    # 결과 출력
    if [ ${#webservers[@]} -eq 0 ]; then
        echo "NONE"
    else
        for ws in "${webservers[@]}"; do
            echo "$ws"
        done
    fi
}

# 메인 실행
detect_webserver
