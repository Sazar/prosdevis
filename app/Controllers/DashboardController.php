<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Database;

class DashboardController
{
    public function index(): void
    {
        Auth::require();
        $companyId = Auth::companyId();

        // Stats devis
        $stats = Database::query(
            "SELECT
                COUNT(*) AS total_quotes,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) AS drafts,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) AS sent,
                SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) AS accepted,
                SUM(CASE WHEN status = 'refused' THEN 1 ELSE 0 END) AS refused,
                SUM(CASE WHEN status = 'accepted' THEN total_ttc ELSE 0 END) AS revenue_quotes
             FROM quotes
             WHERE company_id = ? AND YEAR(issue_date) = YEAR(CURDATE())",
            [$companyId]
        )->fetch();

        // Stats factures
        $invoiceStats = Database::query(
            "SELECT
                SUM(CASE WHEN status = 'paid' THEN total_ttc ELSE 0 END) AS revenue_paid,
                SUM(CASE WHEN status IN ('sent','partial','overdue') THEN (total_ttc - amount_paid) ELSE 0 END) AS revenue_pending,
                COUNT(CASE WHEN status = 'overdue' THEN 1 END) AS overdue_count
             FROM invoices
             WHERE company_id = ? AND YEAR(issue_date) = YEAR(CURDATE())",
            [$companyId]
        )->fetch();

        // Taux de conversion
        $conversionRate = 0;
        if ($stats['total_quotes'] > 0) {
            $conversionRate = round(($stats['accepted'] / $stats['total_quotes']) * 100, 1);
        }

        // Derniers devis
        $recentQuotes = Database::query(
            'SELECT q.*, c.name AS client_name FROM quotes q
             LEFT JOIN clients c ON c.id = q.client_id
             WHERE q.company_id = ?
             ORDER BY q.created_at DESC LIMIT 10',
            [$companyId]
        )->fetchAll();

        $user = Auth::user();
        require __DIR__ . '/../Views/dashboard/index.php';
    }
}
