<?php

namespace App\Services;

use App\Helpers\Database;

/**
 * Export comptable — CSV lisible + FEC NF Z55-200 (France)
 * FEC = Fichier des Écritures Comptables, obligatoire en cas de contrôle fiscal.
 */
class AccountingExport
{
    // ---------------------------------------------------------------
    // CSV SIMPLE — toutes les factures d'une période
    // ---------------------------------------------------------------
    public static function invoicesCsv(int $companyId, string $from, string $to): string
    {
        $invoices = Database::query(
            "SELECT i.number, i.issue_date, i.due_date, i.status,
                    c.name AS client_name, c.email AS client_email, c.siret AS client_siret,
                    i.subtotal_ht, i.total_discount, i.total_vat, i.total_ttc,
                    i.amount_paid, (i.total_ttc - i.amount_paid) AS balance_due,
                    i.currency, i.paid_at, i.created_at
             FROM invoices i
             INNER JOIN clients c ON c.id = i.client_id
             WHERE i.company_id = ?
               AND i.issue_date BETWEEN ? AND ?
             ORDER BY i.issue_date ASC, i.number ASC",
            [$companyId, $from, $to]
        )->fetchAll();

        $headers = [
            'Numéro','Date émission','Date échéance','Statut',
            'Client','Email client','SIRET client',
            'Total HT','Remise','TVA','Total TTC',
            'Encaissé','Solde dû','Devise','Date paiement','Créé le',
        ];

        return self::buildCsv($headers, array_map(fn($r) => [
            $r['number'],
            $r['issue_date'],
            $r['due_date'],
            $r['status'],
            $r['client_name'],
            $r['client_email'],
            $r['client_siret'] ?? '',
            number_format((float)$r['subtotal_ht'],     2, '.', ''),
            number_format((float)$r['total_discount'],  2, '.', ''),
            number_format((float)$r['total_vat'],       2, '.', ''),
            number_format((float)$r['total_ttc'],       2, '.', ''),
            number_format((float)$r['amount_paid'],     2, '.', ''),
            number_format((float)$r['balance_due'],     2, '.', ''),
            $r['currency'],
            $r['paid_at'] ?? '',
            $r['created_at'],
        ], $invoices));
    }

    // ---------------------------------------------------------------
    // FEC NF Z55-200 — fichier des écritures comptables
    // Colonnes obligatoires définies par l'article A.47 A-1 du LPF
    // ---------------------------------------------------------------
    public static function fec(int $companyId, string $from, string $to, string $siret): string
    {
        $lines = Database::query(
            "SELECT
                i.number        AS piece_ref,
                i.issue_date    AS piece_date,
                il.name         AS label,
                il.total_ht     AS debit,
                il.vat_rate,
                il.total_ttc    AS credit,
                i.currency,
                i.due_date,
                c.name          AS tiers,
                c.siret         AS tiers_siret
             FROM invoice_lines il
             INNER JOIN invoices i ON i.id = il.invoice_id
             INNER JOIN clients  c ON c.id = i.client_id
             WHERE i.company_id = ?
               AND i.issue_date BETWEEN ? AND ?
               AND i.status NOT IN ('cancelled')
             ORDER BY i.issue_date ASC, i.number ASC, il.position ASC",
            [$companyId, $from, $to]
        )->fetchAll();

        // En-tête FEC (18 champs obligatoires NF Z55-200)
        $headers = [
            'JournalCode','JournalLib','EcritureNum','EcritureDate',
            'CompteNum','CompteLib','CompAuxNum','CompAuxLib',
            'PieceRef','PieceDate','EcritureLib',
            'Debit','Credit',
            'EcritureLet','DateLet','ValidDate',
            'Montantdevise','Idevise',
        ];

        $rows = [];
        foreach ($lines as $i => $l) {
            $ecritureNum  = 'FEC' . str_pad((string)($i + 1), 6, '0', STR_PAD_LEFT);
            $ecritureDate = str_replace('-', '', substr($l['piece_date'], 0, 10));
            $pieceDate    = str_replace('-', '', substr($l['piece_date'], 0, 10));

            // Ligne produit/service → compte 706xxx (ventes de services)
            $rows[] = [
                'VTE', 'Ventes',
                $ecritureNum, $ecritureDate,
                '706000', 'Prestations de services',
                '', '',
                $l['piece_ref'], $pieceDate,
                self::cleanLabel($l['label']),
                number_format((float)$l['debit'], 2, '.', ''),
                '0.00',
                '', '', $ecritureDate,
                number_format((float)$l['debit'], 2, '.', ''),
                $l['currency'] ?? 'EUR',
            ];

            // Contrepartie client → compte 411xxx
            $rows[] = [
                'VTE', 'Ventes',
                $ecritureNum, $ecritureDate,
                '411000', 'Clients',
                self::cleanSiret($l['tiers_siret'] ?? ''), self::cleanLabel($l['tiers']),
                $l['piece_ref'], $pieceDate,
                self::cleanLabel($l['label']),
                '0.00',
                number_format((float)$l['credit'], 2, '.', ''),
                '', '', $ecritureDate,
                number_format((float)$l['credit'], 2, '.', ''),
                $l['currency'] ?? 'EUR',
            ];
        }

        return self::buildCsv($headers, $rows, '|');
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------
    private static function buildCsv(array $headers, array $rows, string $delimiter = ','): string
    {
        $out = '';
        $out .= self::csvRow($headers, $delimiter);
        foreach ($rows as $row) {
            $out .= self::csvRow($row, $delimiter);
        }
        return $out;
    }

    private static function csvRow(array $fields, string $delimiter): string
    {
        return implode($delimiter, array_map(fn($f) => self::csvEscape((string)$f, $delimiter), $fields)) . "\r\n";
    }

    private static function csvEscape(string $value, string $delimiter): string
    {
        // Pour le FEC (délimiteur |), pas de guillemets requis
        if ($delimiter === '|') {
            return str_replace(['|', "\r", "\n"], [' ', '', ''], $value);
        }
        if (str_contains($value, $delimiter) || str_contains($value, '"') || str_contains($value, "\n")) {
            return '"' . str_replace('"', '""', $value) . '"';
        }
        return $value;
    }

    private static function cleanLabel(string $s): string
    {
        return preg_replace('/[|"\r\n;]/', ' ', $s);
    }

    private static function cleanSiret(?string $s): string
    {
        return preg_replace('/\s/', '', $s ?? '');
    }
}
