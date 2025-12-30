<?php

namespace App\Controllers;

use App\Models\User;
use Core\Utilities\Validator;
use Core\Utilities\Mailer;
use Core\Security\Security;

class AuthController
{
    private $userModel;
    private $mailer;

    public function __construct()
    {
        $this->userModel = new User();
        $config = require __DIR__ . '/../../config/app.php';
        $this->mailer = new Mailer($config);
    }

    public function showRegister()
    {
        include __DIR__ . '/../Views/auth/register.php';
    }

    public function register()
    {
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => $_POST['password'] ?? '',
            'password_confirmation' => $_POST['password_confirmation'] ?? ''
        ];

        $validator = Validator::make($data, [
            'name' => 'required|min:2|max:100',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed'
        ]);

        if (!$validator->validate()) {
            http_response_code(422);
            echo json_encode(['errors' => $validator->errors()]);
            return;
        }

        if (!Security::rateLimit('register_' . $_SERVER['REMOTE_ADDR'], 5, 3600)) {
            http_response_code(429);
            echo json_encode(['error' => 'Too many registration attempts']);
            return;
        }

        try {
            $userId = $this->userModel->register($data);
            $user = $this->userModel->find($userId);

            $this->mailer->sendVerificationEmail($user['email'], $user['email_verification_token']);

            echo json_encode([
                'success' => true,
                'message' => 'Registration successful. Please check your email to verify your account.'
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Registration failed']);
        }
    }

    public function showLogin()
    {
        include __DIR__ . '/../Views/auth/login.php';
    }

    public function login()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!Security::rateLimit('login_' . $_SERVER['REMOTE_ADDR'], 5, 300)) {
            http_response_code(429);
            echo json_encode(['error' => 'Too many login attempts']);
            return;
        }

        $user = $this->userModel->login($email, $password);

        if (!$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            return;
        }

        echo json_encode([
            'success' => true,
            'user' => $user
        ]);
    }

    public function logout()
    {
        $this->userModel->logout();

        echo json_encode([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    public function verifyEmail()
    {
        $token = $_GET['token'] ?? '';

        if ($this->userModel->verifyEmail($token)) {
            echo "Email verified successfully! You can now log in.";
        } else {
            http_response_code(400);
            echo "Invalid or expired verification token.";
        }
    }

    public function forgotPassword()
    {
        $email = $_POST['email'] ?? '';

        $token = $this->userModel->createPasswordResetToken($email);

        if ($token) {
            $this->mailer->sendPasswordReset($email, $token);
        }

        echo json_encode([
            'success' => true,
            'message' => 'If the email exists, a password reset link has been sent.'
        ]);
    }

    public function resetPassword()
    {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';

        $validator = Validator::make([
            'password' => $password,
            'password_confirmation' => $passwordConfirmation
        ], [
            'password' => 'required|min:8|confirmed'
        ]);

        if (!$validator->validate()) {
            http_response_code(422);
            echo json_encode(['errors' => $validator->errors()]);
            return;
        }

        if ($this->userModel->resetPassword($token, $password)) {
            echo json_encode([
                'success' => true,
                'message' => 'Password reset successfully'
            ]);
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid or expired token']);
        }
    }
}
