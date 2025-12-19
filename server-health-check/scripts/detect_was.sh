#!/bin/bash

################################################################################
# WAS 감지 스크립트
# 지원: Tomcat, JEUS, WebLogic, JBoss EAP
################################################################################

detect_was() {
    local was_list=()

    # Tomcat 감지
    if pgrep -f "catalina" > /dev/null 2>&1 || pgrep -f "org.apache.catalina" > /dev/null 2>&1; then
        local tomcat_pids=$(pgrep -f "catalina" | tr '\n' ',')
        local tomcat_home=$(ps -ef | grep catalina | grep -v grep | grep -oP 'catalina.home=\K[^ ]+' | head -1)
        local tomcat_version="unknown"

        if [ -n "$tomcat_home" ] && [ -f "$tomcat_home/lib/catalina.jar" ]; then
            tomcat_version=$(java -cp "$tomcat_home/lib/catalina.jar" org.apache.catalina.util.ServerInfo 2>/dev/null | grep "Server version" | cut -d: -f2 | xargs)
        fi

        was_list+=("tomcat|$tomcat_version|$tomcat_home|$tomcat_pids")
    fi

    # JEUS 감지
    if pgrep -f "jeus.server.JeusServer" > /dev/null 2>&1 || pgrep -f "jeus" > /dev/null 2>&1; then
        local jeus_pids=$(pgrep -f "jeus" | tr '\n' ',')
        local jeus_home=$(ps -ef | grep jeus | grep -v grep | grep -oP 'JEUS_HOME=\K[^ ]+' | head -1)
        local jeus_version="unknown"

        if [ -z "$jeus_home" ]; then
            jeus_home=$(ps -ef | grep jeus | grep -v grep | awk '{for(i=1;i<=NF;i++) if($i ~ /jeus/) print $i}' | head -1)
        fi

        if [ -n "$jeus_home" ] && [ -f "$jeus_home/version.txt" ]; then
            jeus_version=$(cat "$jeus_home/version.txt" 2>/dev/null | head -1)
        fi

        was_list+=("jeus|$jeus_version|$jeus_home|$jeus_pids")
    fi

    # WebLogic 감지
    if pgrep -f "weblogic.Server" > /dev/null 2>&1 || pgrep -f "weblogic" > /dev/null 2>&1; then
        local weblogic_pids=$(pgrep -f "weblogic" | tr '\n' ',')
        local weblogic_home=$(ps -ef | grep weblogic | grep -v grep | grep -oP 'weblogic.home=\K[^ ]+' | head -1)
        local weblogic_version="unknown"

        if [ -z "$weblogic_home" ]; then
            weblogic_home=$(ps -ef | grep weblogic | grep -v grep | grep -oP '\-Dweblogic.home=\K[^ ]+' | head -1)
        fi

        was_list+=("weblogic|$weblogic_version|$weblogic_home|$weblogic_pids")
    fi

    # JBoss EAP 감지
    if pgrep -f "jboss" > /dev/null 2>&1 || pgrep -f "org.jboss" > /dev/null 2>&1; then
        local jboss_pids=$(pgrep -f "jboss" | tr '\n' ',')
        local jboss_home=$(ps -ef | grep jboss | grep -v grep | grep -oP 'jboss.home.dir=\K[^ ]+' | head -1)
        local jboss_version="unknown"

        if [ -z "$jboss_home" ]; then
            jboss_home=$(ps -ef | grep jboss | grep -v grep | grep -oP '\-Djboss.home.dir=\K[^ ]+' | head -1)
        fi

        was_list+=("jboss-eap|$jboss_version|$jboss_home|$jboss_pids")
    fi

    # 결과 출력
    if [ ${#was_list[@]} -eq 0 ]; then
        echo "NONE"
    else
        for was in "${was_list[@]}"; do
            echo "$was"
        done
    fi
}

# 메인 실행
detect_was
