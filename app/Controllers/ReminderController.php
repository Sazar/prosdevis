<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Session;
use App\Models\Reminder;
use App\Services\ReminderMailer;

class ReminderController
{
    /**
     * GET /invoices/{id}/reminders — historique des relances d'une facture
     */
    public function history(int $invoiceId): void
    {
        Auth::require();

        $history = Reminder::historyForInvoice($invoiceId);
        $title   = 'Historique des relances';

        ob_start();
        require __DIR__ . '/../Views/reminders/history.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    /**
     * POST /invoices/{id}/reminders/send — envoi manuel d'une relance
     */
    public function send(int $invoiceId): void
    {
        Auth::require();
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) { http_response_code(403); exit; }

        $companyId = Auth::companyId();

        // Charger la facture avec jointures client + société
        $invoice = \App\Helpers\Database::query(
            'SELECT i.*, c.name AS client_name, c.email AS client_email,
                    co.name AS company_name, co.email AS company_email
             FROM invoices i
             INNER JOIN clients  c  ON c.id  = i.client_id
             INNER JOIN companies co ON co.id = i.company_id
             WHERE i.id = ? AND i.company_id = ? LIMIT 1',
            [$invoiceId, $companyId]
        )->fetch();

        if (!$invoice) {
            Session::flash('error', 'Facture introuvable.');
            header('Location: /invoices');
            exit;
        }

        $level = Reminder::nextLevel($invoiceId);
        if ($level > 3) {
            Session::flash('error', 'Niveau de relance maximum (3) déjà atteint pour cette facture.');
            header('Location: /invoices/' . $invoiceId);
            exit;
        }

        $sent = ReminderMailer::send($invoice, $level);
        Reminder::log($invoiceId, $companyId, $level, $sent, $invoice['client_email'] ?? '');

        Session::flash(
            $sent ? 'success' : 'warning',
            $sent
                ? "Relance niveau {$level} envoyée à {$invoice['client_email']}."
                : "Relance enregistrée mais l'envoi email a échoué. Vérifiez la configuration mail."
        );

        header('Location: /invoices/' . $invoiceId);
        exit;
    }
}
