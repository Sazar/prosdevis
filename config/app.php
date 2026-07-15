<?php

return [
    'name'    => $_ENV['APP_NAME']   ?? 'ProsDevis',
    'url'     => $_ENV['APP_URL']    ?? 'http://localhost',
    'env'     => $_ENV['APP_ENV']    ?? 'production',
    'debug'   => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'secret'  => $_ENV['APP_SECRET'] ?? '',
    'session' => [
        'lifetime'  => 7200,
        'secure'    => true,
        'httponly'  => true,
        'samesite'  => 'Strict',
    ],
    'rate_limit' => [
        'login_max_attempts' => 5,
        'lockout_minutes'    => 15,
    ],
];
