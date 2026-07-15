<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Session;
use App\Helpers\Database;
use App\Models\Quote;

class QuoteController
{
    public function index(): void
    {
        Auth::require();
        $companyId = Auth::companyId();

        $filters = [
            'status'    => $_GET['status'] ?? '',
            'search'    => $_GET['q']      ?? '',
            'client_id' => $_GET['client'] ?? '',
        ];
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $quotes = Quote::list($companyId, $filters, $page);
        $user   = Auth::user();

        $title = $pageTitle = 'Devis';
        $activeNav = 'quotes';

        ob_start();
        require __DIR__ . '/../Views/quotes/index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    public function create(): void
    {
        Auth::require();
        $companyId = Auth::companyId();

        $clients  = Database::query('SELECT id, name, email FROM clients WHERE company_id=? AND is_active=1 ORDER BY name', [$companyId])->fetchAll();
        $products = Database::query('SELECT id, reference, name, unit, unit_price, vat_rate FROM products WHERE company_id=? AND is_active=1 ORDER BY name', [$companyId])->fetchAll();
        $vatRates = Database::query("SELECT rate, label FROM vat_rates WHERE country='FR' AND is_active=1 ORDER BY rate DESC")->fetchAll();
        $user     = Auth::user();

        $title = $pageTitle = 'Nouveau devis';
        $activeNav = 'quotes';

        ob_start();
        require __DIR__ . '/../Views/quotes/form.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    public function store(): void
    {
        Auth::require();
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) { http_response_code(403); die('CSRF error'); }

        $companyId = Auth::companyId();
        $userId    = Auth::id();

        $errors = $this->validateQuoteForm($_POST);
        if (!empty($errors)) {
            Session::flash('error', implode('<br>', $errors));
            header('Location: /quotes/new');
            exit;
        }

        try {
            $number  = Quote::generateNumber($companyId);
            $lines   = $this->parseLines($_POST);
            $quoteId = Quote::create([
                'company_id'    => $companyId,
                'created_by'    => $userId,
                'client_id'     => (int)$_POST['client_id'],
                'number'        => $number,
                'title'         => trim($_POST['title']),
                'description'   => trim($_POST['description'] ?? ''),
                'issue_date'    => $_POST['issue_date'],
                'validity_date' => $_POST['validity_date'],
                'currency'      => $_POST['currency'] ?? 'EUR',
                'country_vat'   => $_POST['country_vat'] ?? 'FR',
                'discount_type' => $_POST['discount_type'] ?? null,
                'discount_value'=> (float)($_POST['discount_value'] ?? 0),
                'deposit_percent'=>(float)($_POST['deposit_percent'] ?? 0),
                'notes'         => trim($_POST['notes'] ?? ''),
                'internal_notes'=> trim($_POST['internal_notes'] ?? ''),
                'payment_terms' => trim($_POST['payment_terms'] ?? ''),
                'status'        => isset($_POST['send_now']) ? 'sent' : 'draft',
            ], $lines);

            Database::query('INSERT INTO activity_logs (user_id,company_id,action,entity_type,entity_id) VALUES (?,?,?,?,?)',
                [$userId, $companyId, 'quote.create', 'quote', $quoteId]);

            Session::flash('success', 'Devis ' . $number . ' créé avec succès !');
            header('Location: /quotes/' . $quoteId);
            exit;
        } catch (\Throwable $e) {
            error_log($e->getMessage());
            Session::flash('error', 'Une erreur est survenue. Veuillez réessayer.');
            header('Location: /quotes/new');
            exit;
        }
    }

    public function show(int $id): void
    {
        Auth::require();
        $companyId = Auth::companyId();
        $quote     = Quote::findById($id, $companyId);

        if (!$quote) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }

        $user      = Auth::user();
        $title     = $quote['number'];
        $pageTitle = $quote['number'];
        $activeNav = 'quotes';

        ob_start();
        require __DIR__ . '/../Views/quotes/show.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    public function edit(int $id): void
    {
        Auth::require();
        $companyId = Auth::companyId();
        $quote     = Quote::findById($id, $companyId);

        if (!$quote || in_array($quote['status'], ['converted', 'refused'])) {
            Session::flash('error', 'Ce devis ne peut plus être modifié.');
            header('Location: /quotes/' . $id);
            exit;
        }

        $clients  = Database::query('SELECT id, name, email FROM clients WHERE company_id=? AND is_active=1 ORDER BY name', [$companyId])->fetchAll();
        $products = Database::query('SELECT id, reference, name, unit, unit_price, vat_rate FROM products WHERE company_id=? AND is_active=1 ORDER BY name', [$companyId])->fetchAll();
        $vatRates = Database::query("SELECT rate, label FROM vat_rates WHERE country='FR' AND is_active=1 ORDER BY rate DESC")->fetchAll();
        $user     = Auth::user();

        $title = $pageTitle = 'Modifier ' . $quote['number'];
        $activeNav = 'quotes';

        ob_start();
        require __DIR__ . '/../Views/quotes/form.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    public function update(int $id): void
    {
        Auth::require();
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) { http_response_code(403); die('CSRF error'); }

        $companyId = Auth::companyId();
        $userId    = Auth::id();

        $errors = $this->validateQuoteForm($_POST);
        if (!empty($errors)) {
            Session::flash('error', implode('<br>', $errors));
            header('Location: /quotes/' . $id . '/edit');
            exit;
        }

        try {
            $lines = $this->parseLines($_POST);
            Quote::update($id, $companyId, [
                'client_id'     => (int)$_POST['client_id'],
                'title'         => trim($_POST['title']),
                'description'   => trim($_POST['description'] ?? ''),
                'issue_date'    => $_POST['issue_date'],
                'validity_date' => $_POST['validity_date'],
                'discount_type' => $_POST['discount_type'] ?? null,
                'discount_value'=> (float)($_POST['discount_value'] ?? 0),
                'deposit_percent'=>(float)($_POST['deposit_percent'] ?? 0),
                'notes'         => trim($_POST['notes'] ?? ''),
                'internal_notes'=> trim($_POST['internal_notes'] ?? ''),
                'payment_terms' => trim($_POST['payment_terms'] ?? ''),
            ], $lines);

            Database::query('INSERT INTO activity_logs (user_id,company_id,action,entity_type,entity_id) VALUES (?,?,?,?,?)',
                [$userId, $companyId, 'quote.update', 'quote', $id]);

            Session::flash('success', 'Devis mis à jour.');
            header('Location: /quotes/' . $id);
            exit;
        } catch (\Throwable $e) {
            Session::flash('error', 'Erreur : ' . $e->getMessage());
            header('Location: /quotes/' . $id . '/edit');
            exit;
        }
    }

    public function updateStatus(int $id): void
    {
        Auth::require();
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) { http_response_code(403); exit; }

        $companyId = Auth::companyId();
        $allowed   = ['sent','viewed','accepted','refused','expired'];
        $newStatus = $_POST['status'] ?? '';

        if (!in_array($newStatus, $allowed)) {
            Session::flash('error', 'Statut invalide.');
            header('Location: /quotes/' . $id);
            exit;
        }

        Database::query(
            'UPDATE quotes SET status=? WHERE id=? AND company_id=?',
            [$newStatus, $id, $companyId]
        );

        Database::query('INSERT INTO activity_logs (user_id,company_id,action,entity_type,entity_id,meta) VALUES (?,?,?,?,?,?)',
            [Auth::id(), $companyId, 'quote.status_change', 'quote', $id, json_encode(['status'=>$newStatus])]);

        Session::flash('success', 'Statut mis à jour.');
        header('Location: /quotes/' . $id);
        exit;
    }

    public function sendEmail(int $id): void
    {
        Auth::require();
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) { http_response_code(403); exit; }

        $companyId = Auth::companyId();
        $quote     = Quote::findById($id, $companyId);
        if (!$quote) { http_response_code(404); exit; }

        $to      = filter_var($_POST['to'] ?? '', FILTER_VALIDATE_EMAIL);
        $subject = strip_tags($_POST['subject'] ?? 'Devis');
        $message = strip_tags($_POST['message'] ?? '');

        if (!$to) {
            Session::flash('error', 'Adresse email invalide.');
            header('Location: /quotes/' . $id);
            exit;
        }

        // Email via mail() natif — à remplacer par PHPMailer/SMTP en production
        $headers  = 'From: ' . ($quote['company_email'] ?? 'noreply@prosdevis.fr') . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $sent = mail($to, $subject, $message, $headers);

        if ($sent) {
            // Marquer le devis comme envoyé
            Database::query('UPDATE quotes SET status="sent" WHERE id=? AND company_id=? AND status="draft"', [$id, $companyId]);
            Database::query('INSERT INTO activity_logs (user_id,company_id,action,entity_type,entity_id,meta) VALUES (?,?,?,?,?,?)',
                [Auth::id(), $companyId, 'quote.sent', 'quote', $id, json_encode(['to'=>$to])]);
            Session::flash('success', 'Email envoyé à ' . $to . '.');
        } else {
            Session::flash('error', 'Échec de l\'envoi. Vérifiez la configuration email du serveur.');
        }

        header('Location: /quotes/' . $id);
        exit;
    }

    public function duplicate(int $id): void
    {
        Auth::require();
        $companyId = Auth::companyId();
        $quote     = Quote::findById($id, $companyId);
        if (!$quote) { http_response_code(404); exit; }

        try {
            $number  = Quote::generateNumber($companyId);
            $newId   = Quote::create([
                'company_id'    => $companyId,
                'created_by'    => Auth::id(),
                'client_id'     => $quote['client_id'],
                'number'        => $number,
                'title'         => '[Copie] ' . $quote['title'],
                'description'   => $quote['description'],
                'issue_date'    => date('Y-m-d'),
                'validity_date' => date('Y-m-d', strtotime('+30 days')),
                'currency'      => $quote['currency'],
                'country_vat'   => $quote['country_vat'],
                'discount_type' => $quote['discount_type'],
                'discount_value'=> $quote['discount_value'],
                'deposit_percent'=>$quote['deposit_percent'],
                'notes'         => $quote['notes'],
                'internal_notes'=> $quote['internal_notes'],
                'payment_terms' => $quote['payment_terms'],
                'status'        => 'draft',
            ], $quote['lines']);

            Session::flash('success', 'Devis dupliqué : ' . $number);
            header('Location: /quotes/' . $newId . '/edit');
        } catch (\Throwable $e) {
            Session::flash('error', 'Erreur lors de la duplication.');
            header('Location: /quotes/' . $id);
        }
        exit;
    }

    public function convertToInvoice(int $id): void
    {
        Auth::require();
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) { http_response_code(403); exit; }

        $companyId = Auth::companyId();
        try {
            $invoiceId = Quote::convertToInvoice($id, $companyId, Auth::id());
            Session::flash('success', 'Facture générée avec succès !');
            header('Location: /invoices/' . $invoiceId);
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
            header('Location: /quotes/' . $id);
        }
        exit;
    }

    // ---- Helpers privés ----

    private function validateQuoteForm(array $post): array
    {
        $errors = [];
        if (empty($post['client_id']))         $errors[] = 'Client obligatoire.';
        if (empty($post['title']))             $errors[] = 'Titre obligatoire.';
        if (empty($post['validity_date']))     $errors[] = 'Date de validité obligatoire.';
        if (empty($post['lines']['name'][0])) $errors[] = 'Au moins une ligne est requise.';
        return $errors;
    }

    private function parseLines(array $post): array
    {
        $lines = [];
        $names = $post['lines']['name'] ?? [];
        foreach ($names as $i => $name) {
            if (empty(trim($name))) continue;
            $lines[] = [
                'product_id'    => $post['lines']['product_id'][$i]  ?? null,
                'type'          => $post['lines']['type'][$i]         ?? 'service',
                'reference'     => $post['lines']['reference'][$i]    ?? null,
                'name'          => trim($name),
                'description'   => $post['lines']['description'][$i]  ?? null,
                'unit'          => $post['lines']['unit'][$i]          ?? 'forfait',
                'quantity'      => (float)($post['lines']['quantity'][$i]   ?? 1),
                'unit_price'    => (float)($post['lines']['unit_price'][$i] ?? 0),
                'vat_rate'      => (float)($post['lines']['vat_rate'][$i]   ?? 20),
                'discount_type' => $post['lines']['discount_type'][$i] ?? null,
                'discount_value'=> (float)($post['lines']['discount_value'][$i] ?? 0),
            ];
        }
        return $lines;
    }
}
