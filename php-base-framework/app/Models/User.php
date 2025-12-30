<?php

namespace App\Models;

use Core\Database\Model;
use Core\Security\Security;

class User extends Model
{
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password', 'provider', 'provider_id'];
    protected $hidden = ['password'];

    public function register($data)
    {
        $data['password'] = Security::hashPassword($data['password']);
        $data['email_verification_token'] = Security::generateToken();
        $data['email_verified_at'] = null;

        return $this->create($data);
    }

    public function login($email, $password)
    {
        $user = $this->first('email', $email);

        if (!$user) {
            return false;
        }

        if (!Security::verifyPassword($password, $user['password'])) {
            return false;
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];

        $this->update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);

        return $this->toArray($user);
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_unset();
        session_destroy();

        return true;
    }

    public function verifyEmail($token)
    {
        $user = $this->first('email_verification_token', $token);

        if (!$user) {
            return false;
        }

        return $this->update($user['id'], [
            'email_verified_at' => date('Y-m-d H:i:s'),
            'email_verification_token' => null
        ]);
    }

    public function createPasswordResetToken($email)
    {
        $user = $this->first('email', $email);

        if (!$user) {
            return false;
        }

        $token = Security::generateToken();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $this->update($user['id'], [
            'password_reset_token' => $token,
            'password_reset_expires_at' => $expiresAt
        ]);

        return $token;
    }

    public function resetPassword($token, $newPassword)
    {
        $user = $this->first('password_reset_token', $token);

        if (!$user) {
            return false;
        }

        if (strtotime($user['password_reset_expires_at']) < time()) {
            return false;
        }

        $hashedPassword = Security::hashPassword($newPassword);

        return $this->update($user['id'], [
            'password' => $hashedPassword,
            'password_reset_token' => null,
            'password_reset_expires_at' => null
        ]);
    }

    public function findOrCreateFromProvider($provider, $providerData)
    {
        $user = $this->first('provider_id', $providerData['id']);

        if ($user) {
            return $user;
        }

        $userId = $this->create([
            'name' => $providerData['name'],
            'email' => $providerData['email'],
            'provider' => $provider,
            'provider_id' => $providerData['id'],
            'email_verified_at' => date('Y-m-d H:i:s')
        ]);

        return $this->find($userId);
    }
}
