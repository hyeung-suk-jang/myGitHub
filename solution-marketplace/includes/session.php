<?php
/**
 * 세션 관리 클래스
 */

class Session {
    /**
     * 세션 시작
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * 세션 값 설정
     */
    public static function set($key, $value) {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * 세션 값 가져오기
     */
    public static function get($key, $default = null) {
        self::start();
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
    }

    /**
     * 세션 값 존재 확인
     */
    public static function has($key) {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * 세션 값 삭제
     */
    public static function remove($key) {
        self::start();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * 모든 세션 삭제
     */
    public static function destroy() {
        self::start();
        session_unset();
        session_destroy();
    }

    /**
     * 로그인 확인
     */
    public static function isLoggedIn() {
        return self::has('user_id');
    }

    /**
     * 사용자 정보 설정
     */
    public static function setUser($user) {
        self::set('user_id', $user['id']);
        self::set('user_email', $user['email']);
        self::set('user_name', $user['name']);
        self::set('user_type', $user['user_type']);
    }

    /**
     * 사용자 ID 가져오기
     */
    public static function getUserId() {
        return self::get('user_id');
    }

    /**
     * 사용자 타입 가져오기
     */
    public static function getUserType() {
        return self::get('user_type');
    }

    /**
     * 관리자 확인
     */
    public static function isAdmin() {
        return self::get('user_type') === 'admin';
    }

    /**
     * 셀러 확인
     */
    public static function isSeller() {
        return self::get('user_type') === 'seller';
    }

    /**
     * Flash 메시지 설정
     */
    public static function setFlash($key, $message) {
        self::set('flash_' . $key, $message);
    }

    /**
     * Flash 메시지 가져오기 (한 번만 표시)
     */
    public static function getFlash($key) {
        $message = self::get('flash_' . $key);
        self::remove('flash_' . $key);
        return $message;
    }

    /**
     * CSRF 토큰 생성
     */
    public static function generateCsrfToken() {
        $token = bin2hex(random_bytes(32));
        self::set('csrf_token', $token);
        return $token;
    }

    /**
     * CSRF 토큰 검증
     */
    public static function validateCsrfToken($token) {
        return self::get('csrf_token') === $token;
    }
}
