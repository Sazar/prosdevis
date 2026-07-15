<?php

namespace App\Models;

use App\Helpers\Database;

class Reminder
{
    /**
     * Seuils d'escalade : [niveau => jours de retard minimum]
     */
    public const LEVELS = [
        1 => 1,
        2 => 7,
        3 => 21,
    ];

    /**
     * Factures éligibles à une relance selon les seuils d'escalade.
     * Une facture n'est relancée qu'une seule fois par niveau.
     */
    public static function getEligible(): array
    {
        return Database::query(
            "SELECT
                i.id,
                i.number,
                i.due_date,
                i.total_ttc,
                i.amount_paid,
                i.total_ttc - i.amount_paid AS balance_due,
                DATEDIFF(CURDATE(), i.due_date) AS days_overdue,
                i.company_id,
                c.name  AS client_name,
                c.email AS client_email,
                co.name AS company_name,
                co.email AS company_email,
                COALESCE(MAX(r.level), 0) AS last_level,
                MAX(r.sent_at) AS last_sent_at
             FROM invoices i
             INNER JOIN clients  c  ON c.id  = i.client_id
             INNER JOIN companies co ON co.id = i.company_id
             LEFT  JOIN invoice_reminders r ON r.invoice_id = i.id
             WHERE i.status IN ('sent','partial','overdue')
               AND i.due_date < CURDATE()
             GROUP BY i.id
             HAVING
                (last_level = 0 AND days_overdue >= 1)
             OR (last_level = 1 AND days_overdue >= 7  AND DATEDIFF(CURDATE(), last_sent_at) >= 6)
             OR (last_level = 2 AND days_overdue >= 21 AND DATEDIFF(CURDATE(), last_sent_at) >= 13)
             ORDER BY days_overdue DESC",
            []
        )->fetchAll();
    }

    /**
     * Calcule le prochain niveau de relance pour une facture donnée.
     */
    public static function nextLevel(int $invoiceId): int
    {
        $row = Database::query(
            'SELECT COALESCE(MAX(level), 0) AS last FROM invoice_reminders WHERE invoice_id = ?',
            [$invoiceId]
        )->fetch();
        return min((int)($row['last'] ?? 0) + 1, 3);
    }

    /**
     * Enregistre l'envoi d'une relance dans la table invoice_reminders
     * et met à jour le statut de la facture en 'overdue'.
     */
    public static function log(int $invoiceId, int $companyId, int $level, bool $sent, string $to): void
    {
        Database::query(
            'INSERT INTO invoice_reminders (invoice_id, company_id, level, sent_to, sent, sent_at, created_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
            [$invoiceId, $companyId, $level, $to, (int)$sent]
        );

        Database::query(
            "UPDATE invoices SET status = 'overdue' WHERE id = ? AND status IN ('sent','partial')",
            [$invoiceId]
        );

        Database::query(
            'INSERT INTO activity_logs (company_id, action, entity_type, entity_id, new_values, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())',
            [$companyId, 'invoice.reminder_sent', 'invoice', $invoiceId,
             json_encode(['level' => $level, 'sent_to' => $to, 'sent' => $sent], JSON_UNESCAPED_UNICODE)]
        );
    }

    /**
     * Historique des relances pour une facture.
     */
    public static function historyForInvoice(int $invoiceId): array
    {
        return Database::query(
            'SELECT r.*, u.first_name, u.last_name
             FROM invoice_reminders r
             LEFT JOIN users u ON u.id = r.triggered_by
             WHERE r.invoice_id = ?
             ORDER BY r.sent_at DESC',
            [$invoiceId]
        )->fetchAll();
    }
}
