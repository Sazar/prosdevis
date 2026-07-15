<?php

namespace App\Models;

use App\Helpers\Database;

class Invoice
{
    public static function generateNumber(int $companyId): string
    {
        $year = (int) date('Y');

        Database::beginTransaction();
        try {
            $seq = Database::query(
                'SELECT last_number FROM number_sequences WHERE company_id = ? AND type = ? AND year = ? FOR UPDATE',
                [$companyId, 'invoice', $year]
            )->fetch();

            if (!$seq) {
                Database::query(
                    'INSERT INTO number_sequences (company_id, type, year, last_number) VALUES (?, ?, ?, 1)',
                    [$companyId, 'invoice', $year]
                );
                $next = 1;
            } else {
                $next = $seq['last_number'] + 1;
                Database::query(
                    'UPDATE number_sequences SET last_number = ? WHERE company_id = ? AND type = ? AND year = ?',
                    [$next, $companyId, 'invoice', $year]
                );
            }

            Database::commit();
            return sprintf('FAC-%d-%04d', $year, $next);
        } catch (\Throwable $e) {
            Database::rollback();
            throw $e;
        }
    }

    public static function create(array $data, array $lines): int
    {
        Database::beginTransaction();
        try {
            ['ht' => $ht, 'vat' => $vat, 'discount' => $discount, 'ttc' => $ttc] = self::computeTotals($lines, $data);

            Database::query(
                'INSERT INTO invoices
                (company_id, client_id, quote_id, created_by, number, status, title, description,
                 issue_date, due_date, paid_date, currency, country_vat,
                 discount_type, discount_value, deposit_percent,
                 subtotal_ht, total_discount, total_vat, total_ttc,
                 amount_paid, balance_due, notes, internal_notes, payment_terms)
                VALUES
                (:company_id, :client_id, :quote_id, :created_by, :number, :status, :title, :description,
                 :issue_date, :due_date, :paid_date, :currency, :country_vat,
                 :discount_type, :discount_value, :deposit_percent,
                 :subtotal_ht, :total_discount, :total_vat, :total_ttc,
                 :amount_paid, :balance_due, :notes, :internal_notes, :payment_terms)',
                [
                    'company_id'     => $data['company_id'],
                    'client_id'      => $data['client_id'],
                    'quote_id'       => $data['quote_id'] ?? null,
                    'created_by'     => $data['created_by'],
                    'number'         => $data['number'],
                    'status'         => $data['status'] ?? 'draft',
                    'title'          => $data['title'],
                    'description'    => $data['description'] ?? null,
                    'issue_date'     => $data['issue_date'] ?? date('Y-m-d'),
                    'due_date'       => $data['due_date'],
                    'paid_date'      => $data['paid_date'] ?? null,
                    'currency'       => $data['currency'] ?? 'EUR',
                    'country_vat'    => $data['country_vat'] ?? 'FR',
                    'discount_type'  => $data['discount_type'] ?? null,
                    'discount_value' => $data['discount_value'] ?? 0,
                    'deposit_percent'=> $data['deposit_percent'] ?? 0,
                    'subtotal_ht'    => $ht,
                    'total_discount' => $discount,
                    'total_vat'      => $vat,
                    'total_ttc'      => $ttc,
                    'amount_paid'    => $data['amount_paid'] ?? 0,
                    'balance_due'    => $ttc - ($data['amount_paid'] ?? 0),
                    'notes'          => $data['notes'] ?? null,
                    'internal_notes' => $data['internal_notes'] ?? null,
                    'payment_terms'  => $data['payment_terms'] ?? null,
                ]
            );

            $invoiceId = (int) Database::lastInsertId();
            self::saveLines($invoiceId, $lines);

            Database::commit();
            return $invoiceId;
        } catch (\Throwable $e) {
            Database::rollback();
            throw $e;
        }
    }

    public static function saveLines(int $invoiceId, array $lines): void
    {
        foreach ($lines as $i => $line) {
            $lineHt  = round(($line['quantity'] ?? 1) * ($line['unit_price'] ?? 0), 4);
            $disc    = 0;
            if (!empty($line['discount_value'])) {
                $disc = $line['discount_type'] === 'percent'
                    ? $lineHt * ($line['discount_value'] / 100)
                    : (float)$line['discount_value'];
            }
            $lineHtNet = $lineHt - $disc;
            $lineTtc   = $lineHtNet * (1 + ($line['vat_rate'] ?? 20) / 100);

            Database::query(
                'INSERT INTO invoice_lines
                (invoice_id, position, type, reference, name, description, unit, quantity, unit_price, vat_rate, discount_type, discount_value, total_ht, total_ttc)
                VALUES (:invoice_id,:position,:type,:reference,:name,:description,:unit,:quantity,:unit_price,:vat_rate,:discount_type,:discount_value,:total_ht,:total_ttc)',
                [
                    'invoice_id'     => $invoiceId,
                    'position'       => $i,
                    'type'           => $line['type'] ?? 'service',
                    'reference'      => $line['reference'] ?? null,
                    'name'           => $line['name'],
                    'description'    => $line['description'] ?? null,
                    'unit'           => $line['unit'] ?? 'forfait',
                    'quantity'       => $line['quantity'] ?? 1,
                    'unit_price'     => $line['unit_price'] ?? 0,
                    'vat_rate'       => $line['vat_rate'] ?? 20,
                    'discount_type'  => $line['discount_type'] ?? null,
                    'discount_value' => $line['discount_value'] ?? 0,
                    'total_ht'       => round($lineHtNet, 4),
                    'total_ttc'      => round($lineTtc, 4),
                ]
            );
        }
    }

    public static function computeTotals(array $lines, array $data = []): array
    {
        $subtotalHt = 0;
        $totalVat   = 0;

        foreach ($lines as $line) {
            $lineHt  = ($line['quantity'] ?? 1) * ($line['unit_price'] ?? 0);
            $disc    = 0;
            if (!empty($line['discount_value'])) {
                $disc = $line['discount_type'] === 'percent'
                    ? $lineHt * ($line['discount_value'] / 100)
                    : (float)$line['discount_value'];
            }
            $lineHtNet  = $lineHt - $disc;
            $subtotalHt += $lineHtNet;
            $totalVat   += $lineHtNet * (($line['vat_rate'] ?? 20) / 100);
        }

        $globalDiscount = 0;
        if (!empty($data['discount_value'])) {
            $globalDiscount = $data['discount_type'] === 'percent'
                ? $subtotalHt * ($data['discount_value'] / 100)
                : (float)$data['discount_value'];
        }

        $netHt = $subtotalHt - $globalDiscount;
        $ttc   = $netHt + $totalVat;

        return [
            'ht'       => round($subtotalHt, 4),
            'vat'      => round($totalVat, 4),
            'discount' => round($globalDiscount, 4),
            'ttc'      => round($ttc, 4),
        ];
    }

    public static function findById(int $id, int $companyId): ?array
    {
        $invoice = Database::query(
            'SELECT i.*, c.name AS client_name, c.email AS client_email, c.address AS client_address, c.zip AS client_zip, c.city AS client_city, c.country AS client_country, c.siret AS client_siret, c.vat_number AS client_vat
             FROM invoices i
             LEFT JOIN clients c ON c.id = i.client_id
             WHERE i.id = ? AND i.company_id = ? LIMIT 1',
            [$id, $companyId]
        )->fetch();

        if (!$invoice) return null;

        $invoice['lines'] = Database::query(
            'SELECT * FROM invoice_lines WHERE invoice_id = ? ORDER BY position ASC',
            [$id]
        )->fetchAll();

        return $invoice;
    }

    public static function list(int $companyId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where  = ['i.company_id = :company_id'];
        $params = ['company_id' => $companyId];

        if (!empty($filters['status'])) {
            $where[] = 'i.status = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(i.number LIKE :search OR c.name LIKE :search OR i.title LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['overdue'])) {
            $where[] = 'i.due_date < CURDATE() AND i.status NOT IN ("paid","cancelled")';
        }

        $sql = 'SELECT i.id, i.number, i.title, i.status, i.total_ttc, i.balance_due, i.issue_date, i.due_date, c.name AS client_name
                FROM invoices i
                LEFT JOIN clients c ON c.id = i.client_id
                WHERE ' . implode(' AND ', $where) . '
                ORDER BY i.created_at DESC
                LIMIT :limit OFFSET :offset';

        $params['limit']  = $perPage;
        $params['offset'] = ($page - 1) * $perPage;

        return Database::query($sql, $params)->fetchAll();
    }

    public static function markAsPaid(int $id, int $companyId, float $amount, ?string $paidDate = null): void
    {
        $invoice = self::findById($id, $companyId);
        if (!$invoice) throw new \RuntimeException('Facture introuvable');

        $newPaid   = (float)$invoice['amount_paid'] + $amount;
        $balance   = max(0, (float)$invoice['total_ttc'] - $newPaid);
        $newStatus = $balance <= 0.0001 ? 'paid' : 'partial';

        Database::query(
            'UPDATE invoices SET amount_paid = ?, balance_due = ?, status = ?, paid_date = ? WHERE id = ? AND company_id = ?',
            [$newPaid, $balance, $newStatus, $paidDate ?: date('Y-m-d'), $id, $companyId]
        );
    }

    public static function markAsSent(int $id, int $companyId): void
    {
        Database::query('UPDATE invoices SET status = "sent" WHERE id = ? AND company_id = ? AND status = "draft"', [$id, $companyId]);
    }
}
