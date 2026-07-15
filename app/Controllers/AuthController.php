<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Session;
use App\Models\User;
use App\Helpers\Database;

class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            header('Location: /dashboard');
            exit;
        }
        $csrf = Session::csrf();
        $error = Session::flash('login_error');
        require __DIR__ . '/../Views/auth/login.php';
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        // Vérification CSRF
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) {
            Session::flash('login_error', 'Requête invalide. Veuillez réessayer.');
            header('Location: /login');
            exit;
        }

        $email    = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $ip       = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';

        // Validation basique
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
            Session::flash('login_error', 'Identifiants invalides.');
            header('Location: /login');
            exit;
        }

        $user = User::findByEmail($email);

        if (!$user || !$user['is_active']) {
            // Timing safe
            password_verify($password, '$2y$12$invalid_hash_to_prevent_timing');
            Session::flash('login_error', 'Identifiants incorrects.');
            header('Location: /login');
            exit;
        }

        // Compte bloqué
        if (User::isLocked($user)) {
            Session::flash('login_error', 'Compte temporairement bloqué. Veuillez réessayer dans quelques minutes.');
            header('Location: /login');
            exit;
        }

        // Vérification mot de passe
        if (!User::verifyPassword($password, $user['password'])) {
            User::incrementLoginAttempts($user['id']);
            $config = require __DIR__ . '/../../config/app.php';
            if ($user['login_attempts'] + 1 >= $config['rate_limit']['login_max_attempts']) {
                User::lockAccount($user['id'], $config['rate_limit']['lockout_minutes']);
                Session::flash('login_error', 'Trop de tentatives. Compte bloqué 15 minutes.');
            } else {
                Session::flash('login_error', 'Identifiants incorrects.');
            }
            header('Location: /login');
            exit;
        }

        // Vérification email
        if (!$user['email_verified']) {
            Session::flash('login_error', 'Veuillez vérifier votre email avant de vous connecter.');
            header('Location: /login');
            exit;
        }

        // Connexion
        User::updateLoginMeta($user['id'], $ip);
        Auth::login($user);

        // Log activité
        Database::query(
            'INSERT INTO activity_logs (user_id, company_id, action, ip, user_agent) VALUES (?, ?, ?, ?, ?)',
            [$user['id'], $user['company_id'], 'user.login', $ip, $_SERVER['HTTP_USER_AGENT'] ?? '']
        );

        $redirect = Session::flash('redirect_after_login') ?? '/dashboard';
        header('Location: ' . $redirect);
        exit;
    }

    public function logout(): void
    {
        $userId    = Auth::id();
        $companyId = Auth::companyId();
        if ($userId) {
            Database::query(
                'INSERT INTO activity_logs (user_id, company_id, action, ip) VALUES (?, ?, ?, ?)',
                [$userId, $companyId, 'user.logout', $_SERVER['REMOTE_ADDR'] ?? '']
            );
        }
        Auth::logout();
        header('Location: /login');
        exit;
    }

    public function showForgotPassword(): void
    {
        $csrf    = Session::csrf();
        $success = Session::flash('forgot_success');
        $error   = Session::flash('forgot_error');
        require __DIR__ . '/../Views/auth/forgot-password.php';
    }

    public function forgotPassword(): void
    {
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) {
            Session::flash('forgot_error', 'Requête invalide.');
            header('Location: /forgot-password');
            exit;
        }
        $email = strtolower(trim($_POST['email'] ?? ''));
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $token = User::setResetToken($email);
            if ($token) {
                // TODO: envoyer email avec PHPMailer
            }
        }
        // Réponse toujours positive (sécurité)
        Session::flash('forgot_success', 'Si cet email existe, un lien de réinitialisation vous a été envoyé.');
        header('Location: /forgot-password');
        exit;
    }
}
