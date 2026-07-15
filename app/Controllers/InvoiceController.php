<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Session;
use App\Helpers\Database;
use App\Models\Invoice;

class InvoiceController
{
    public function index(): void
    {
        Auth::require();
        $companyId = Auth::companyId();

        $filters = [
            'status'  => $_GET['status'] ?? '',
            'search'  => $_GET['q'] ?? '',
            'overdue' => $_GET['overdue'] ?? '',
        ];

        $invoices = Invoice::list($companyId, $filters, max(1, (int)($_GET['page'] ?? 1)));
        $user = Auth::user();
        $title = $pageTitle = 'Factures';
        $activeNav = 'invoices';

        ob_start();
        require __DIR__ . '/../Views/invoices/index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    public function show(int $id): void
    {
        Auth::require();
        $companyId = Auth::companyId();
        $invoice = Invoice::findById($id, $companyId);

        if (!$invoice) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }

        $user = Auth::user();
        $title = $pageTitle = $invoice['number'];
        $activeNav = 'invoices';

        ob_start();
        require __DIR__ . '/../Views/invoices/show.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    public function markAsPaid(int $id): void
    {
        Auth::require();
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) { http_response_code(403); exit; }

        $companyId = Auth::companyId();
        $amount    = (float)($_POST['amount'] ?? 0);
        $paidDate  = $_POST['paid_date'] ?? date('Y-m-d');

        if ($amount <= 0) {
            Session::flash('error', 'Montant invalide.');
            header('Location: /invoices/' . $id);
            exit;
        }

        try {
            Invoice::markAsPaid($id, $companyId, $amount, $paidDate);
            Database::query('INSERT INTO activity_logs (user_id, company_id, action, entity_type, entity_id, meta) VALUES (?,?,?,?,?,?)', [
                Auth::id(), $companyId, 'invoice.paid', 'invoice', $id, json_encode(['amount' => $amount, 'paid_date' => $paidDate])
            ]);
            Session::flash('success', 'Paiement enregistré.');
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
        }

        header('Location: /invoices/' . $id);
        exit;
    }

    public function sendReminder(int $id): void
    {
        Auth::require();
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) { http_response_code(403); exit; }

        $companyId = Auth::companyId();
        $invoice   = Invoice::findById($id, $companyId);
        if (!$invoice) { http_response_code(404); exit; }

        $to = filter_var($_POST['to'] ?? $invoice['client_email'], FILTER_VALIDATE_EMAIL);
        if (!$to) {
            Session::flash('error', 'Email invalide pour la relance.');
            header('Location: /invoices/' . $id);
            exit;
        }

        $subject = 'Relance facture ' . $invoice['number'];
        $message = "Bonjour,\n\nSauf erreur de notre part, la facture {$invoice['number']} d'un montant de {$invoice['total_ttc']} EUR n'est pas encore réglée.\nMerci de procéder au paiement avant le {$invoice['due_date']}.\n\nCordialement.";
        $headers = "Content-Type: text/plain; charset=UTF-8\r\n";

        $sent = mail($to, $subject, $message, $headers);
        if ($sent) {
            Database::query('INSERT INTO activity_logs (user_id, company_id, action, entity_type, entity_id, meta) VALUES (?,?,?,?,?,?)', [
                Auth::id(), $companyId, 'invoice.reminder_sent', 'invoice', $id, json_encode(['to' => $to])
            ]);
            Session::flash('success', 'Relance envoyée à ' . $to . '.');
        } else {
            Session::flash('error', 'Échec de l\'envoi de la relance.');
        }

        header('Location: /invoices/' . $id);
        exit;
    }
}
