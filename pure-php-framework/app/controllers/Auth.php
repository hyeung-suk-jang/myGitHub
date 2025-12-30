<?php
/**
 * 인증 컨트롤러
 */

require_once CORE_PATH . '/controller.php';

class AuthController extends Controller {
    /**
     * 회원가입 폼
     */
    public function register() {
        $this->view('auth/register', [
            'title' => '회원가입'
        ]);
    }

    /**
     * 회원가입 처리
     */
    public function doRegister() {
        if (!is_post()) {
            $this->redirect('/register');
            return;
        }

        // CSRF 검증
        if (!csrf_verify(post('csrf_token'))) {
            $this->view('auth/register', [
                'title' => '회원가입',
                'error' => 'CSRF 토큰이 유효하지 않습니다.'
            ]);
            return;
        }

        $username = post('username');
        $email = post('email');
        $password = post('password');
        $password_confirm = post('password_confirm');

        // 유효성 검증
        $errors = [];

        if (empty($username)) {
            $errors[] = '사용자명을 입력하세요.';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = '유효한 이메일을 입력하세요.';
        }

        if (empty($password) || strlen($password) < 6) {
            $errors[] = '비밀번호는 최소 6자 이상이어야 합니다.';
        }

        if ($password !== $password_confirm) {
            $errors[] = '비밀번호가 일치하지 않습니다.';
        }

        if (!empty($errors)) {
            $this->view('auth/register', [
                'title' => '회원가입',
                'errors' => $errors,
                'old' => $_POST
            ]);
            return;
        }

        // 사용자 존재 여부 확인
        $existing = $this->db->queryOne(
            "SELECT id FROM users WHERE email = ? OR username = ?",
            [$email, $username]
        );

        if ($existing) {
            $this->view('auth/register', [
                'title' => '회원가입',
                'error' => '이미 존재하는 이메일 또는 사용자명입니다.',
                'old' => $_POST
            ]);
            return;
        }

        // 사용자 생성
        $user_id = $this->db->insertData('users', [
            'username' => $username,
            'email' => $email,
            'password' => hash_password($password),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if ($user_id) {
            // 자동 로그인
            session_set('user_id', $user_id);
            session_set('username', $username);

            $this->redirect('/');
        } else {
            $this->view('auth/register', [
                'title' => '회원가입',
                'error' => '회원가입 중 오류가 발생했습니다.',
                'old' => $_POST
            ]);
        }
    }

    /**
     * 로그인 폼
     */
    public function login() {
        $this->view('auth/login', [
            'title' => '로그인'
        ]);
    }

    /**
     * 로그인 처리
     */
    public function doLogin() {
        if (!is_post()) {
            $this->redirect('/login');
            return;
        }

        // CSRF 검증
        if (!csrf_verify(post('csrf_token'))) {
            $this->view('auth/login', [
                'title' => '로그인',
                'error' => 'CSRF 토큰이 유효하지 않습니다.'
            ]);
            return;
        }

        $email = post('email');
        $password = post('password');

        // 사용자 조회
        $user = $this->db->queryOne(
            "SELECT * FROM users WHERE email = ?",
            [$email]
        );

        if (!$user || !verify_password($password, $user['password'])) {
            $this->view('auth/login', [
                'title' => '로그인',
                'error' => '이메일 또는 비밀번호가 올바르지 않습니다.',
                'old' => $_POST
            ]);
            return;
        }

        // 로그인 성공
        session_set('user_id', $user['id']);
        session_set('username', $user['username']);

        $this->redirect('/');
    }

    /**
     * 로그아웃
     */
    public function logout() {
        session_destroy_all();
        $this->redirect('/');
    }
}
