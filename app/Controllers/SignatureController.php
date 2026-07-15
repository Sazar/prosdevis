<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Database;
use App\Helpers\Session;
use App\Models\Quote;
use App\Models\Signature;

class SignatureController
{
    public function request(int $id): void
    {
        Auth::require();
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) { http_response_code(403); exit; }

        $companyId = Auth::companyId();
        $quote     = Quote::findById($id, $companyId);
        if (!$quote) {
            http_response_code(404);
            exit;
        }

        try {
            $token = Signature::ensureToken($id, $companyId);
            $link  = (isset($_SERVER['HTTP_HOST']) ? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] : '') . '/sign/' . $token;

            $to      = $quote['client_email'];
            $subject = 'Signature du devis ' . $quote['number'];
            $message = "Bonjour,\n\nVeuillez consulter et signer votre devis {$quote['number']} en suivant ce lien :\n{$link}\n\nCordialement.";
            $headers = "Content-Type: text/plain; charset=UTF-8\r\n";

            $sent = $to ? mail($to, $subject, $message, $headers) : false;

            Database::query(
                'INSERT INTO activity_logs (user_id, company_id, action, entity_type, entity_id, new_values, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, NOW())',
                [Auth::id(), $companyId, 'quote.signature_requested', 'quote', $id, json_encode(['link' => $link, 'email_sent' => (bool) $sent], JSON_UNESCAPED_UNICODE)]
            );

            Session::flash('success', $sent
                ? 'Lien de signature envoyé au client.'
                : 'Lien de signature généré. Envoi email à vérifier côté serveur.');
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
        }

        header('Location: /quotes/' . $id);
        exit;
    }

    public function show(string $token): void
    {
        $quote = Signature::findQuoteByToken($token);
        if (!$quote) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }

        $title = 'Signature du devis ' . $quote['number'];
        ob_start();
        require __DIR__ . '/../Views/signatures/show.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/public.php';
    }

    public function sign(string $token): void
    {
        $signerName = trim($_POST['signer_name'] ?? '');
        $signerEmail = trim($_POST['signer_email'] ?? '');
        $signatureData = $_POST['signature_data'] ?? '';
        $consent = !empty($_POST['consent']);

        if ($signerName === '' || $signerEmail === '' || $signatureData === '' || !$consent) {
            Session::flash('error', 'Veuillez compléter tous les champs et signer.');
            header('Location: /sign/' . $token);
            exit;
        }

        $quoteId = Signature::signQuote($token, [
            'signer_name'  => $signerName,
            'signer_email' => $signerEmail,
            'signature_data' => $signatureData,
            'signed_at'    => date('c'),
            'user_agent'   => $_SERVER['HTTP_USER_AGENT'] ?? null,
        ], $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

        if (!$quoteId) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }

        header('Location: /sign/' . $token . '?signed=1');
        exit;
    }

    public function decline(string $token): void
    {
        $reason = trim($_POST['reason'] ?? '');
        if ($reason === '') {
            Session::flash('error', 'Merci d’indiquer la raison du refus.');
            header('Location: /sign/' . $token);
            exit;
        }

        $quoteId = Signature::declineQuote($token, $reason, $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        if (!$quoteId) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }

        header('Location: /sign/' . $token . '?declined=1');
        exit;
    }
}
