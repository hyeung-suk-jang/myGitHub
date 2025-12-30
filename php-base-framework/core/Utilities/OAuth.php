<?php

namespace Core\Utilities;

class OAuth
{
    private $config;
    private $provider;

    public function __construct($provider, $config)
    {
        $this->provider = $provider;
        $this->config = $config[$provider] ?? [];
    }

    public function getAuthorizationUrl()
    {
        $state = bin2hex(random_bytes(16));

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['oauth_state'] = $state;

        $params = [
            'client_id' => $this->config['client_id'],
            'redirect_uri' => $this->config['redirect_uri'],
            'response_type' => 'code',
            'scope' => $this->config['scope'],
            'state' => $state
        ];

        return $this->config['authorization_url'] . '?' . http_build_query($params);
    }

    public function getAccessToken($code)
    {
        $params = [
            'client_id' => $this->config['client_id'],
            'client_secret' => $this->config['client_secret'],
            'code' => $code,
            'redirect_uri' => $this->config['redirect_uri'],
            'grant_type' => 'authorization_code'
        ];

        $ch = curl_init($this->config['token_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function getUserInfo($accessToken)
    {
        $ch = curl_init($this->config['user_info_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $accessToken
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $userData = json_decode($response, true);

        return $this->normalizeUserData($userData);
    }

    private function normalizeUserData($data)
    {
        switch ($this->provider) {
            case 'google':
                return [
                    'id' => $data['sub'] ?? $data['id'],
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'avatar' => $data['picture'] ?? null
                ];

            case 'facebook':
                return [
                    'id' => $data['id'],
                    'name' => $data['name'],
                    'email' => $data['email'] ?? null,
                    'avatar' => $data['picture']['data']['url'] ?? null
                ];

            case 'kakao':
                return [
                    'id' => $data['id'],
                    'name' => $data['properties']['nickname'] ?? '',
                    'email' => $data['kakao_account']['email'] ?? null,
                    'avatar' => $data['properties']['profile_image'] ?? null
                ];

            default:
                return $data;
        }
    }

    public function verifyState($state)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return isset($_SESSION['oauth_state']) && $_SESSION['oauth_state'] === $state;
    }
}
