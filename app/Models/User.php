<?php

namespace App\Models;

use App\Helpers\Database;

class User
{
    public static function findByEmail(string $email): ?array
    {
        $user = Database::query(
            'SELECT * FROM users WHERE email = ? AND deleted_at IS NULL LIMIT 1',
            [$email]
        )->fetch();
        return $user ?: null;
    }

    public static function findById(int $id): ?array
    {
        $user = Database::query(
            'SELECT id, company_id, email, first_name, last_name, role, avatar_path, theme, totp_enabled, is_active FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1',
            [$id]
        )->fetch();
        return $user ?: null;
    }

    public static function create(array $data): int
    {
        Database::query(
            'INSERT INTO users (company_id, email, password, first_name, last_name, role, email_token, gdpr_consent, gdpr_consent_at)
             VALUES (:company_id, :email, :password, :first_name, :last_name, :role, :email_token, 1, NOW())',
            [
                'company_id'  => $data['company_id'],
                'email'       => strtolower(trim($data['email'])),
                'password'    => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
                'first_name'  => $data['first_name'],
                'last_name'   => $data['last_name'],
                'role'        => $data['role'] ?? 'collaborator',
                'email_token' => bin2hex(random_bytes(32)),
            ]
        );
        return (int) Database::lastInsertId();
    }

    public static function updateLoginMeta(int $id, string $ip): void
    {
        Database::query(
            'UPDATE users SET last_login_at = NOW(), last_login_ip = ?, login_attempts = 0 WHERE id = ?',
            [$ip, $id]
        );
    }

    public static function incrementLoginAttempts(int $id): void
    {
        Database::query(
            'UPDATE users SET login_attempts = login_attempts + 1 WHERE id = ?',
            [$id]
        );
    }

    public static function lockAccount(int $id, int $minutes = 15): void
    {
        Database::query(
            'UPDATE users SET locked_until = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?',
            [$minutes, $id]
        );
    }

    public static function isLocked(array $user): bool
    {
        if ($user['locked_until'] === null) return false;
        return strtotime($user['locked_until']) > time();
    }

    public static function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    public static function setResetToken(string $email): ?string
    {
        $token = bin2hex(random_bytes(32));
        $rows = Database::query(
            'UPDATE users SET reset_token = ?, reset_expires_at = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ? AND deleted_at IS NULL',
            [$token, $email]
        )->rowCount();
        return $rows > 0 ? $token : null;
    }

    public static function findByResetToken(string $token): ?array
    {
        $user = Database::query(
            'SELECT * FROM users WHERE reset_token = ? AND reset_expires_at > NOW() AND deleted_at IS NULL LIMIT 1',
            [$token]
        )->fetch();
        return $user ?: null;
    }

    public static function updatePassword(int $id, string $newPassword): void
    {
        Database::query(
            'UPDATE users SET password = ?, reset_token = NULL, reset_expires_at = NULL WHERE id = ?',
            [password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]), $id]
        );
    }
}
