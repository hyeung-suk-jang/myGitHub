<?php

namespace Core\Security;

class Security
{
    public static function generateToken()
    {
        return bin2hex(random_bytes(32));
    }

    public static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }

    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    public static function encrypt($data, $key)
    {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }

    public static function decrypt($data, $key)
    {
        $data = base64_decode($data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }

    public static function sanitizeInput($data)
    {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    public static function escapeOutput($data)
    {
        if (is_array($data)) {
            return array_map([self::class, 'escapeOutput'], $data);
        }
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function generateCSRFToken()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = self::generateToken();
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();

        return $token;
    }

    public static function verifyCSRFToken($token)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }

        $tokenAge = time() - $_SESSION['csrf_token_time'];
        if ($tokenAge > 3600) {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function rateLimit($key, $maxAttempts = 5, $timeWindow = 60)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $rateLimitKey = "rate_limit_{$key}";

        if (!isset($_SESSION[$rateLimitKey])) {
            $_SESSION[$rateLimitKey] = [
                'attempts' => 0,
                'reset_time' => time() + $timeWindow
            ];
        }

        if (time() > $_SESSION[$rateLimitKey]['reset_time']) {
            $_SESSION[$rateLimitKey] = [
                'attempts' => 0,
                'reset_time' => time() + $timeWindow
            ];
        }

        $_SESSION[$rateLimitKey]['attempts']++;

        return $_SESSION[$rateLimitKey]['attempts'] <= $maxAttempts;
    }
}
