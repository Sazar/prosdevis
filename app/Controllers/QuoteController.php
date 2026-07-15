<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Session;
use App\Helpers\Database;
use App\Models\Quote;
use App\Models\Client;

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

        $title     = 'Devis';
        $pageTitle = 'Mes devis';
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

        $title     = 'Nouveau devis';
        $pageTitle = 'Créer un devis';
        $activeNav = 'quotes';

        ob_start();
        require __DIR__ . '/../Views/quotes/form.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    public function store(): void
    {
        Auth::require();

        if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            die('CSRF error');
        }

        $companyId = Auth::companyId();
        $userId    = Auth::id();

        // Validation
        $errors = $this->validateQuoteForm($_POST);
        if (!empty($errors)) {
            Session::flash('error', implode('<br>', $errors));
            header('Location: /quotes/new');
            exit;
        }

        try {
            $number = Quote::generateNumber($companyId);
            $lines  = $this->parseLines($_POST);

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

            // Log
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
            http_response_code(403);
            Session::flash('error', 'Ce devis ne peut plus être modifié.');
            header('Location: /quotes/' . $id);
            exit;
        }

        $clients  = Database::query('SELECT id, name, email FROM clients WHERE company_id=? AND is_active=1 ORDER BY name', [$companyId])->fetchAll();
        $products = Database::query('SELECT id, reference, name, unit, unit_price, vat_rate FROM products WHERE company_id=? AND is_active=1 ORDER BY name', [$companyId])->fetchAll();
        $vatRates = Database::query("SELECT rate, label FROM vat_rates WHERE country='FR' AND is_active=1 ORDER BY rate DESC")->fetchAll();
        $user     = Auth::user();

        $title     = 'Modifier ' . $quote['number'];
        $pageTitle = 'Modifier le devis';
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
            Session::flash('error', 'Erreur: ' . $e->getMessage());
            header('Location: /quotes/' . $id . '/edit');
            exit;
        }
    }

    public function convertToInvoice(int $id): void
    {
        Auth::require();
        Auth::requireRole('admin', 'collaborator');
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
        if (empty($post['client_id'])) $errors[] = 'Client obligatoire.';
        if (empty($post['title']))     $errors[] = 'Titre obligatoire.';
        if (empty($post['validity_date'])) $errors[] = 'Date de validité obligatoire.';
        if (empty($post['lines']['name'][0])) $errors[] = 'Au moins une ligne de prestation est requise.';
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
