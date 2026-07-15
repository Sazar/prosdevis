<?php

namespace App\Services;

/**
 * Mailer — abstraction envoi email
 *
 * Ordre de préférence :
 *   1. SMTP natif (socket PHP, sans dépendance)
 *   2. API HTTP Brevo (ex-Sendinblue)
 *   3. API HTTP Mailgun
 *   4. Fallback : mail() PHP
 *
 * Configuration via constantes ou variables d'environnement (.env) :
 *   MAIL_DRIVER   = smtp | brevo | mailgun | mail
 *   MAIL_FROM     = noreply@monsaas.fr
 *   MAIL_FROM_NAME = ProsDevis
 *
 *   SMTP_HOST / SMTP_PORT / SMTP_USER / SMTP_PASS / SMTP_ENCRYPTION (tls|ssl|none)
 *   BREVO_API_KEY
 *   MAILGUN_API_KEY / MAILGUN_DOMAIN / MAILGUN_REGION (eu|us)
 */
class Mailer
{
    public static function send(
        string $to,
        string $subject,
        string $htmlBody,
        string $textBody = '',
        array  $replyTo  = []
    ): bool {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $driver = strtolower(self::env('MAIL_DRIVER', 'mail'));

        return match ($driver) {
            'smtp'    => self::sendSmtp($to, $subject, $htmlBody, $textBody, $replyTo),
            'brevo'   => self::sendBrevo($to, $subject, $htmlBody, $textBody, $replyTo),
            'mailgun' => self::sendMailgun($to, $subject, $htmlBody, $textBody, $replyTo),
            default   => self::sendNative($to, $subject, $htmlBody, $textBody),
        };
    }

    // -------------------------------------------------------------------
    // DRIVER 1 — SMTP natif
    // -------------------------------------------------------------------
    private static function sendSmtp(
        string $to, string $subject, string $html, string $text, array $replyTo
    ): bool {
        $host       = self::env('SMTP_HOST', 'localhost');
        $port       = (int) self::env('SMTP_PORT', '587');
        $user       = self::env('SMTP_USER', '');
        $pass       = self::env('SMTP_PASS', '');
        $encryption = strtolower(self::env('SMTP_ENCRYPTION', 'tls'));
        $from       = self::env('MAIL_FROM', 'noreply@localhost');
        $fromName   = self::env('MAIL_FROM_NAME', 'ProsDevis');

        $prefix = match ($encryption) {
            'ssl'   => 'ssl://',
            default => '',
        };

        $socket = @fsockopen($prefix . $host, $port, $errno, $errstr, 10);
        if (!$socket) return false;

        $read = fn() => fgets($socket, 1024);
        $write = fn(string $cmd) => fputs($socket, $cmd . "\r\n");

        $read(); // banner
        $write('EHLO ' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        while (($line = $read()) && substr($line, 3, 1) === '-') {}

        if ($encryption === 'tls') {
            $write('STARTTLS');
            $read();
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($socket);
                return false;
            }
            $write('EHLO ' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
            while (($line = $read()) && substr($line, 3, 1) === '-') {}
        }

        if ($user !== '') {
            $write('AUTH LOGIN');
            $read();
            $write(base64_encode($user));
            $read();
            $write(base64_encode($pass));
            $res = $read();
            if ((int)$res !== 235) { fclose($socket); return false; }
        }

        $write('MAIL FROM:<' . $from . '>');
        $read();
        $write('RCPT TO:<' . $to . '>');
        $read();
        $write('DATA');
        $read();

        $boundary = md5(uniqid('', true));
        $msg  = 'From: ' . self::mimeEncode($fromName) . ' <' . $from . ">\r\n";
        $msg .= 'To: ' . $to . "\r\n";
        $msg .= 'Subject: ' . self::mimeEncode($subject) . "\r\n";
        if ($replyTo) {
            $msg .= 'Reply-To: ' . implode(', ', $replyTo) . "\r\n";
        }
        $msg .= 'MIME-Version: 1.0' . "\r\n";
        $msg .= 'Content-Type: multipart/alternative; boundary="' . $boundary . '"' . "\r\n\r\n";
        if ($text !== '') {
            $msg .= '--' . $boundary . "\r\n";
            $msg .= 'Content-Type: text/plain; charset=UTF-8' . "\r\n\r\n";
            $msg .= $text . "\r\n\r\n";
        }
        $msg .= '--' . $boundary . "\r\n";
        $msg .= 'Content-Type: text/html; charset=UTF-8' . "\r\n\r\n";
        $msg .= $html . "\r\n\r\n";
        $msg .= '--' . $boundary . '--';
        $msg .= "\r\n.";

        $write($msg);
        $res = $read();
        $write('QUIT');
        fclose($socket);

        return str_starts_with(trim($res), '250');
    }

    // -------------------------------------------------------------------
    // DRIVER 2 — Brevo (ex-Sendinblue) REST API
    // -------------------------------------------------------------------
    private static function sendBrevo(
        string $to, string $subject, string $html, string $text, array $replyTo
    ): bool {
        $apiKey   = self::env('BREVO_API_KEY', '');
        $from     = self::env('MAIL_FROM', 'noreply@localhost');
        $fromName = self::env('MAIL_FROM_NAME', 'ProsDevis');
        if ($apiKey === '') return self::sendNative($to, $subject, $html, $text);

        $payload = json_encode([
            'sender'      => ['name' => $fromName, 'email' => $from],
            'to'          => [['email' => $to]],
            'subject'     => $subject,
            'htmlContent' => $html,
            'textContent' => $text ?: strip_tags($html),
        ]);

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER     => [
                'accept: application/json',
                'api-key: ' . $apiKey,
                'content-type: application/json',
            ],
        ]);
        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $status >= 200 && $status < 300;
    }

    // -------------------------------------------------------------------
    // DRIVER 3 — Mailgun REST API
    // -------------------------------------------------------------------
    private static function sendMailgun(
        string $to, string $subject, string $html, string $text, array $replyTo
    ): bool {
        $apiKey   = self::env('MAILGUN_API_KEY', '');
        $domain   = self::env('MAILGUN_DOMAIN', '');
        $region   = strtolower(self::env('MAILGUN_REGION', 'eu'));
        $from     = self::env('MAIL_FROM', 'noreply@localhost');
        $fromName = self::env('MAIL_FROM_NAME', 'ProsDevis');
        if ($apiKey === '' || $domain === '') return self::sendNative($to, $subject, $html, $text);

        $baseUrl = $region === 'eu'
            ? 'https://api.eu.mailgun.net/v3'
            : 'https://api.mailgun.net/v3';

        $fields = [
            'from'    => $fromName . ' <' . $from . '>',
            'to'      => $to,
            'subject' => $subject,
            'html'    => $html,
            'text'    => $text ?: strip_tags($html),
        ];

        $ch = curl_init($baseUrl . '/' . $domain . '/messages');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($fields),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERPWD        => 'api:' . $apiKey,
        ]);
        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $status >= 200 && $status < 300;
    }

    // -------------------------------------------------------------------
    // DRIVER 4 — mail() natif PHP (fallback)
    // -------------------------------------------------------------------
    private static function sendNative(
        string $to, string $subject, string $html, string $text
    ): bool {
        $from     = self::env('MAIL_FROM', 'noreply@localhost');
        $fromName = self::env('MAIL_FROM_NAME', 'ProsDevis');
        $headers  = implode("\r\n", [
            'From: ' . $fromName . ' <' . $from . '>',
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
        ]);
        return @mail($to, $subject, $html, $headers);
    }

    // -------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------
    private static function env(string $key, string $default = ''): string
    {
        $val = getenv($key);
        if ($val !== false && $val !== '') return $val;
        if (defined($key)) return constant($key);
        return $default;
    }

    private static function mimeEncode(string $str): string
    {
        return '=?UTF-8?B?' . base64_encode($str) . '?=';
    }
}
