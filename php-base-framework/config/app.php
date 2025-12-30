<?php

return [
    'app_name' => 'PHP Base Framework',
    'app_url' => getenv('APP_URL') ?: 'http://localhost',
    'environment' => getenv('APP_ENV') ?: 'development',
    'debug' => getenv('APP_DEBUG') === 'true',
    'timezone' => 'Asia/Seoul',

    // Mail Configuration
    'from' => getenv('MAIL_FROM') ?: 'noreply@example.com',
    'smtp' => false,
    'smtp_host' => getenv('SMTP_HOST') ?: '',
    'smtp_port' => getenv('SMTP_PORT') ?: 587,
    'smtp_username' => getenv('SMTP_USERNAME') ?: '',
    'smtp_password' => getenv('SMTP_PASSWORD') ?: '',
];
