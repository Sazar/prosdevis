<?php

namespace App\Helpers;

class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (!self::$started && session_status() === PHP_SESSION_NONE) {
            $config = require __DIR__ . '/../../config/app.php';
            $s = $config['session'];
            session_set_cookie_params([
                'lifetime' => $s['lifetime'],
                'path'     => '/',
                'secure'   => $s['secure'],
                'httponly' => $s['httponly'],
                'samesite' => $s['samesite'],
            ]);
            session_name('PROSDEVIS_SID');
            session_start();
            self::$started = true;
        }
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        self::start();
        $_SESSION = [];
        session_destroy();
        self::$started = false;
    }

    public static function csrf(): string
    {
        if (!self::has('csrf_token')) {
            self::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return self::get('csrf_token');
    }

    public static function verifyCsrf(string $token): bool
    {
        return hash_equals(self::get('csrf_token', ''), $token);
    }

    public static function flash(string $key, mixed $value = null): mixed
    {
        if ($value !== null) {
            self::set('flash_' . $key, $value);
            return null;
        }
        $v = self::get('flash_' . $key);
        self::remove('flash_' . $key);
        return $v;
    }
}
