<?php

return [
    'google' => [
        'client_id' => getenv('GOOGLE_CLIENT_ID') ?: '',
        'client_secret' => getenv('GOOGLE_CLIENT_SECRET') ?: '',
        'redirect_uri' => getenv('APP_URL') . '/auth/google/callback',
        'authorization_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
        'token_url' => 'https://oauth2.googleapis.com/token',
        'user_info_url' => 'https://www.googleapis.com/oauth2/v2/userinfo',
        'scope' => 'email profile',
    ],

    'facebook' => [
        'client_id' => getenv('FACEBOOK_CLIENT_ID') ?: '',
        'client_secret' => getenv('FACEBOOK_CLIENT_SECRET') ?: '',
        'redirect_uri' => getenv('APP_URL') . '/auth/facebook/callback',
        'authorization_url' => 'https://www.facebook.com/v12.0/dialog/oauth',
        'token_url' => 'https://graph.facebook.com/v12.0/oauth/access_token',
        'user_info_url' => 'https://graph.facebook.com/me?fields=id,name,email,picture',
        'scope' => 'email',
    ],

    'kakao' => [
        'client_id' => getenv('KAKAO_CLIENT_ID') ?: '',
        'client_secret' => getenv('KAKAO_CLIENT_SECRET') ?: '',
        'redirect_uri' => getenv('APP_URL') . '/auth/kakao/callback',
        'authorization_url' => 'https://kauth.kakao.com/oauth/authorize',
        'token_url' => 'https://kauth.kakao.com/oauth/token',
        'user_info_url' => 'https://kapi.kakao.com/v2/user/me',
        'scope' => 'profile_nickname,account_email',
    ],
];
