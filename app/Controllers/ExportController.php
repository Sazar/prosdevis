<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Database;
use App\Helpers\Session;
use App\Services\AccountingExport;

class ExportController
{
    /**
     * GET /exports — page de sélection d'export
     */
    public function index(): void
    {
        Auth::require();
        $title = 'Export comptable';
        ob_start();
        require __DIR__ . '/../Views/exports/index.php';
        $content = ob_get_clean();
        require __DIR__ . '/../Views/layouts/app.php';
    }

    /**
     * POST /exports/csv — export CSV factures
     */
    public function csv(): void
    {
        Auth::require();
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) { http_response_code(403); exit; }

        $companyId = Auth::companyId();
        $from = $_POST['from'] ?? date('Y-01-01');
        $to   = $_POST['to']   ?? date('Y-12-31');

        $csv      = AccountingExport::invoicesCsv($companyId, $from, $to);
        $filename = 'prosdevis_factures_' . str_replace('-', '', $from) . '_' . str_replace('-', '', $to) . '.csv';

        $this->logExport($companyId, 'csv', $from, $to);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen("\xEF\xBB\xBF" . $csv));
        echo "\xEF\xBB\xBF"; // BOM UTF-8 pour Excel
        echo $csv;
    }

    /**
     * POST /exports/fec — export FEC NF Z55-200
     */
    public function fec(): void
    {
        Auth::require();
        if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) { http_response_code(403); exit; }

        $companyId = Auth::companyId();
        $from = $_POST['from'] ?? date('Y-01-01');
        $to   = $_POST['to']   ?? date('Y-12-31');

        $company  = Database::query('SELECT siret FROM companies WHERE id = ? LIMIT 1', [$companyId])->fetch();
        $siret    = preg_replace('/\s/', '', $company['siret'] ?? '');

        $fec      = AccountingExport::fec($companyId, $from, $to, $siret);
        $year     = substr($from, 0, 4);
        $filename = $siret . 'FEC' . $year . '.txt'; // Convention DGFiP : SIRETFECannée.txt

        $this->logExport($companyId, 'fec', $from, $to);

        header('Content-Type: text/plain; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($fec));
        echo $fec;
    }

    private function logExport(int $companyId, string $type, string $from, string $to): void
    {
        Database::query(
            'INSERT INTO activity_logs (user_id, company_id, action, entity_type, new_values, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())',
            [Auth::id(), $companyId, 'export.' . $type, 'export',
             json_encode(['from' => $from, 'to' => $to], JSON_UNESCAPED_UNICODE)]
        );
    }
}
