<?php

namespace App\Models;

use App\Helpers\Database;

class Signature
{
    public static function findQuoteByToken(string $token): ?array
    {
        $quote = Database::query(
            'SELECT q.*, c.name AS client_name, c.email AS client_email, c.address AS client_address, c.zip AS client_zip, c.city AS client_city,
                    co.name AS company_name, co.siret AS company_siret, co.vat_number AS company_vat, co.address AS company_address, co.zip AS company_zip, co.city AS company_city, co.legal_mentions, co.payment_terms AS company_payment_terms
             FROM quotes q
             INNER JOIN clients c ON c.id = q.client_id
             INNER JOIN companies co ON co.id = q.company_id
             WHERE q.signature_token = ? LIMIT 1',
            [$token]
        )->fetch();

        if (!$quote) return null;

        $quote['lines'] = Database::query(
            'SELECT * FROM quote_lines WHERE quote_id = ? ORDER BY position ASC',
            [$quote['id']]
        )->fetchAll();

        return $quote;
    }

    public static function ensureToken(int $quoteId, int $companyId): string
    {
        $quote = Database::query('SELECT signature_token FROM quotes WHERE id = ? AND company_id = ? LIMIT 1', [$quoteId, $companyId])->fetch();
        if (!$quote) {
            throw new \RuntimeException('Devis introuvable');
        }

        if (!empty($quote['signature_token'])) {
            return $quote['signature_token'];
        }

        $token = bin2hex(random_bytes(24));
        Database::query('UPDATE quotes SET signature_token = ?, signature_status = IF(signature_status = "none", "pending", signature_status) WHERE id = ? AND company_id = ?', [$token, $quoteId, $companyId]);
        return $token;
    }

    public static function signQuote(string $token, array $payload, string $ip): ?int
    {
        $quote = Database::query('SELECT id, company_id, status, signature_status FROM quotes WHERE signature_token = ? LIMIT 1', [$token])->fetch();
        if (!$quote) {
            return null;
        }

        Database::query(
            'UPDATE quotes
             SET signature_status = "signed",
                 signature_data = ?,
                 signature_ip = ?,
                 signature_at = NOW(),
                 status = IF(status IN ("draft","sent","viewed"), "accepted", status),
                 accepted_at = IF(accepted_at IS NULL, NOW(), accepted_at)
             WHERE id = ?',
            [json_encode($payload, JSON_UNESCAPED_UNICODE), $ip, $quote['id']]
        );

        Database::query(
            'INSERT INTO activity_logs (company_id, action, entity_type, entity_id, new_values, ip, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())',
            [$quote['company_id'], 'quote.signed', 'quote', $quote['id'], json_encode(['signer_name' => $payload['signer_name'] ?? null]), $ip]
        );

        return (int) $quote['id'];
    }

    public static function declineQuote(string $token, string $reason, string $ip): ?int
    {
        $quote = Database::query('SELECT id, company_id FROM quotes WHERE signature_token = ? LIMIT 1', [$token])->fetch();
        if (!$quote) return null;

        Database::query(
            'UPDATE quotes
             SET signature_status = "declined",
                 status = IF(status IN ("draft","sent","viewed"), "refused", status),
                 refused_at = IF(refused_at IS NULL, NOW(), refused_at),
                 signature_data = ?,
                 signature_ip = ?
             WHERE id = ?',
            [json_encode(['decline_reason' => $reason], JSON_UNESCAPED_UNICODE), $ip, $quote['id']]
        );

        Database::query(
            'INSERT INTO activity_logs (company_id, action, entity_type, entity_id, new_values, ip, created_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW())',
            [$quote['company_id'], 'quote.signature_declined', 'quote', $quote['id'], json_encode(['reason' => $reason], JSON_UNESCAPED_UNICODE), $ip]
        );

        return (int) $quote['id'];
    }
}
