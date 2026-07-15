<?php

namespace App\Models;

use App\Helpers\Database;

class Dashboard
{
    public static function kpis(int $companyId): array
    {
        $now   = date('Y-m-d');
        $month = date('Y-m-01');

        // CA facturé ce mois (TTC factures envoyées/payées)
        $caMois = (float) Database::query(
            'SELECT COALESCE(SUM(total_ttc),0) FROM invoices WHERE company_id=? AND status IN ("sent","partial","paid") AND issue_date >= ?',
            [$companyId, $month]
        )->fetchColumn();

        // CA encaissé ce mois
        $caEncaisse = (float) Database::query(
            'SELECT COALESCE(SUM(amount_paid),0) FROM invoices WHERE company_id=? AND paid_date >= ?',
            [$companyId, $month]
        )->fetchColumn();

        // Solde total dû (toutes factures ouvertes)
        $soldeDu = (float) Database::query(
            'SELECT COALESCE(SUM(balance_due),0) FROM invoices WHERE company_id=? AND status NOT IN ("paid","cancelled")',
            [$companyId]
        )->fetchColumn();

        // Devis en attente
        $devisEnAttente = (int) Database::query(
            'SELECT COUNT(*) FROM quotes WHERE company_id=? AND status IN ("draft","sent","viewed")',
            [$companyId]
        )->fetchColumn();

        // Factures en retard
        $facturesEnRetard = (int) Database::query(
            'SELECT COUNT(*) FROM invoices WHERE company_id=? AND due_date < ? AND status NOT IN ("paid","cancelled")',
            [$companyId, $now]
        )->fetchColumn();

        // Taux de conversion devis → facture (30 derniers jours)
        $totalDevis30 = (int) Database::query(
            'SELECT COUNT(*) FROM quotes WHERE company_id=? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)',
            [$companyId]
        )->fetchColumn();
        $convertis30 = (int) Database::query(
            'SELECT COUNT(*) FROM quotes WHERE company_id=? AND status="converted" AND updated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)',
            [$companyId]
        )->fetchColumn();
        $tauxConversion = $totalDevis30 > 0 ? round($convertis30 / $totalDevis30 * 100) : 0;

        return compact('caMois','caEncaisse','soldeDu','devisEnAttente','facturesEnRetard','tauxConversion','totalDevis30','convertis30');
    }

    public static function caMensuel(int $companyId, int $months = 6): array
    {
        $rows = Database::query(
            'SELECT DATE_FORMAT(issue_date,"%Y-%m") AS mois,
                    COALESCE(SUM(total_ttc),0) AS ca_facture,
                    COALESCE(SUM(amount_paid),0) AS ca_encaisse
             FROM invoices
             WHERE company_id=? AND status NOT IN ("cancelled")
               AND issue_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
             GROUP BY mois ORDER BY mois ASC',
            [$companyId, $months]
        )->fetchAll();

        // Remplir les mois manquants
        $result = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $key = date('Y-m', strtotime("-{$i} months"));
            $result[$key] = ['mois' => $key, 'ca_facture' => 0.0, 'ca_encaisse' => 0.0];
        }
        foreach ($rows as $row) {
            if (isset($result[$row['mois']])) {
                $result[$row['mois']] = $row;
            }
        }
        return array_values($result);
    }

    public static function statutsDevis(int $companyId): array
    {
        return Database::query(
            'SELECT status, COUNT(*) AS total FROM quotes WHERE company_id=? GROUP BY status ORDER BY total DESC',
            [$companyId]
        )->fetchAll();
    }

    public static function activiteRecente(int $companyId, int $limit = 10): array
    {
        return Database::query(
            'SELECT al.action, al.entity_type, al.entity_id, al.created_at, u.name AS user_name
             FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             WHERE al.company_id=?
             ORDER BY al.created_at DESC
             LIMIT ?',
            [$companyId, $limit]
        )->fetchAll();
    }

    public static function topClients(int $companyId, int $limit = 5): array
    {
        return Database::query(
            'SELECT c.name, COALESCE(SUM(i.total_ttc),0) AS ca_total, COUNT(i.id) AS nb_factures
             FROM clients c
             LEFT JOIN invoices i ON i.client_id=c.id AND i.company_id=? AND i.status NOT IN ("cancelled")
             WHERE c.company_id=?
             GROUP BY c.id, c.name
             HAVING nb_factures > 0
             ORDER BY ca_total DESC
             LIMIT ?',
            [$companyId, $companyId, $limit]
        )->fetchAll();
    }
}
