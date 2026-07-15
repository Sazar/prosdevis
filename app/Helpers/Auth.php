<?php

namespace App\Helpers;

use App\Models\User;

class Auth
{
    public static function check(): bool
    {
        return Session::has('user_id');
    }

    public static function user(): ?array
    {
        if (!self::check()) return null;
        $userId = Session::get('user_id');
        return User::findById($userId);
    }

    public static function id(): ?int
    {
        return Session::get('user_id');
    }

    public static function companyId(): ?int
    {
        return Session::get('company_id');
    }

    public static function role(): ?string
    {
        return Session::get('user_role');
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'admin';
    }

    public static function isAccountant(): bool
    {
        return in_array(self::role(), ['admin', 'accountant']);
    }

    public static function login(array $user): void
    {
        Session::destroy();
        Session::start();
        session_regenerate_id(true);
        Session::set('user_id', $user['id']);
        Session::set('company_id', $user['company_id']);
        Session::set('user_role', $user['role']);
        Session::set('user_name', $user['first_name'] . ' ' . $user['last_name']);
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    public static function require(): void
    {
        if (!self::check()) {
            Session::flash('redirect_after_login', $_SERVER['REQUEST_URI']);
            header('Location: /login');
            exit;
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::require();
        if (!in_array(self::role(), $roles)) {
            http_response_code(403);
            require __DIR__ . '/../../app/Views/errors/403.php';
            exit;
        }
    }
}
