<?php

namespace App\Models;

use App\Helpers\Database;

class Quote
{
    /**
     * Génère le prochain numéro de devis (séquentiel, non modifiable)
     * Format: DEV-YYYY-XXXX
     */
    public static function generateNumber(int $companyId): string
    {
        $year = (int) date('Y');

        Database::beginTransaction();
        try {
            // Lock la ligne pour éviter les doublons concurrents
            $seq = Database::query(
                'SELECT last_number FROM number_sequences WHERE company_id = ? AND type = ? AND year = ? FOR UPDATE',
                [$companyId, 'quote', $year]
            )->fetch();

            if (!$seq) {
                Database::query(
                    'INSERT INTO number_sequences (company_id, type, year, last_number) VALUES (?, ?, ?, 1)',
                    [$companyId, 'quote', $year]
                );
                $next = 1;
            } else {
                $next = $seq['last_number'] + 1;
                Database::query(
                    'UPDATE number_sequences SET last_number = ? WHERE company_id = ? AND type = ? AND year = ?',
                    [$next, $companyId, 'quote', $year]
                );
            }

            Database::commit();
            return sprintf('DEV-%d-%04d', $year, $next);
        } catch (\Throwable $e) {
            Database::rollback();
            throw $e;
        }
    }

    public static function create(array $data, array $lines): int
    {
        Database::beginTransaction();
        try {
            // Calcul des totaux
            ['ht' => $ht, 'vat' => $vat, 'discount' => $discount, 'ttc' => $ttc] = self::computeTotals($lines, $data);

            Database::query(
                'INSERT INTO quotes
                (company_id, client_id, created_by, number, status, title, description,
                 issue_date, validity_date, currency, country_vat,
                 discount_type, discount_value, deposit_percent,
                 subtotal_ht, total_discount, total_vat, total_ttc,
                 notes, internal_notes, payment_terms)
                VALUES
                (:company_id, :client_id, :created_by, :number, :status, :title, :description,
                 :issue_date, :validity_date, :currency, :country_vat,
                 :discount_type, :discount_value, :deposit_percent,
                 :subtotal_ht, :total_discount, :total_vat, :total_ttc,
                 :notes, :internal_notes, :payment_terms)',
                [
                    'company_id'    => $data['company_id'],
                    'client_id'     => $data['client_id'],
                    'created_by'    => $data['created_by'],
                    'number'        => $data['number'],
                    'status'        => $data['status'] ?? 'draft',
                    'title'         => $data['title'],
                    'description'   => $data['description'] ?? null,
                    'issue_date'    => $data['issue_date'] ?? date('Y-m-d'),
                    'validity_date' => $data['validity_date'],
                    'currency'      => $data['currency'] ?? 'EUR',
                    'country_vat'   => $data['country_vat'] ?? 'FR',
                    'discount_type' => $data['discount_type'] ?? null,
                    'discount_value'=> $data['discount_value'] ?? 0,
                    'deposit_percent'=>$data['deposit_percent'] ?? 0,
                    'subtotal_ht'   => $ht,
                    'total_discount'=> $discount,
                    'total_vat'     => $vat,
                    'total_ttc'     => $ttc,
                    'notes'         => $data['notes'] ?? null,
                    'internal_notes'=> $data['internal_notes'] ?? null,
                    'payment_terms' => $data['payment_terms'] ?? null,
                ]
            );

            $quoteId = (int) Database::lastInsertId();
            self::saveLines($quoteId, $lines);

            Database::commit();
            return $quoteId;
        } catch (\Throwable $e) {
            Database::rollback();
            throw $e;
        }
    }

    public static function update(int $id, int $companyId, array $data, array $lines): void
    {
        Database::beginTransaction();
        try {
            ['ht' => $ht, 'vat' => $vat, 'discount' => $discount, 'ttc' => $ttc] = self::computeTotals($lines, $data);

            Database::query(
                'UPDATE quotes SET
                 client_id=:client_id, title=:title, description=:description,
                 issue_date=:issue_date, validity_date=:validity_date,
                 discount_type=:discount_type, discount_value=:discount_value,
                 deposit_percent=:deposit_percent,
                 subtotal_ht=:subtotal_ht, total_discount=:total_discount,
                 total_vat=:total_vat, total_ttc=:total_ttc,
                 notes=:notes, internal_notes=:internal_notes, payment_terms=:payment_terms,
                 autosave_at=NOW()
                 WHERE id=:id AND company_id=:company_id',
                [
                    'client_id'     => $data['client_id'],
                    'title'         => $data['title'],
                    'description'   => $data['description'] ?? null,
                    'issue_date'    => $data['issue_date'],
                    'validity_date' => $data['validity_date'],
                    'discount_type' => $data['discount_type'] ?? null,
                    'discount_value'=> $data['discount_value'] ?? 0,
                    'deposit_percent'=>$data['deposit_percent'] ?? 0,
                    'subtotal_ht'   => $ht,
                    'total_discount'=> $discount,
                    'total_vat'     => $vat,
                    'total_ttc'     => $ttc,
                    'notes'         => $data['notes'] ?? null,
                    'internal_notes'=> $data['internal_notes'] ?? null,
                    'payment_terms' => $data['payment_terms'] ?? null,
                    'id'            => $id,
                    'company_id'    => $companyId,
                ]
            );

            // Supprimer et recréer les lignes
            Database::query('DELETE FROM quote_lines WHERE quote_id = ?', [$id]);
            self::saveLines($id, $lines);

            Database::commit();
        } catch (\Throwable $e) {
            Database::rollback();
            throw $e;
        }
    }

    private static function saveLines(int $quoteId, array $lines): void
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
                'INSERT INTO quote_lines
                (quote_id, product_id, position, type, reference, name, description, unit, quantity, unit_price, vat_rate, discount_type, discount_value, total_ht, total_ttc)
                VALUES (:quote_id,:product_id,:position,:type,:reference,:name,:description,:unit,:quantity,:unit_price,:vat_rate,:discount_type,:discount_value,:total_ht,:total_ttc)',
                [
                    'quote_id'      => $quoteId,
                    'product_id'    => $line['product_id'] ?? null,
                    'position'      => $i,
                    'type'          => $line['type'] ?? 'service',
                    'reference'     => $line['reference'] ?? null,
                    'name'          => $line['name'],
                    'description'   => $line['description'] ?? null,
                    'unit'          => $line['unit'] ?? 'forfait',
                    'quantity'      => $line['quantity'] ?? 1,
                    'unit_price'    => $line['unit_price'] ?? 0,
                    'vat_rate'      => $line['vat_rate'] ?? 20,
                    'discount_type' => $line['discount_type'] ?? null,
                    'discount_value'=> $line['discount_value'] ?? 0,
                    'total_ht'      => round($lineHtNet, 4),
                    'total_ttc'     => round($lineTtc, 4),
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

        // Remise globale
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
        $quote = Database::query(
            'SELECT q.*, c.name AS client_name, c.email AS client_email, c.address AS client_address, c.zip AS client_zip, c.city AS client_city, c.country AS client_country, c.siret AS client_siret, c.vat_number AS client_vat
             FROM quotes q
             LEFT JOIN clients c ON c.id = q.client_id
             WHERE q.id = ? AND q.company_id = ? LIMIT 1',
            [$id, $companyId]
        )->fetch();
        if (!$quote) return null;

        $quote['lines'] = Database::query(
            'SELECT * FROM quote_lines WHERE quote_id = ? ORDER BY position ASC',
            [$id]
        )->fetchAll();

        return $quote;
    }

    public static function list(int $companyId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where  = ['q.company_id = :company_id'];
        $params = ['company_id' => $companyId];

        if (!empty($filters['status'])) {
            $where[]          = 'q.status = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['client_id'])) {
            $where[]             = 'q.client_id = :client_id';
            $params['client_id'] = $filters['client_id'];
        }
        if (!empty($filters['search'])) {
            $where[]           = '(q.number LIKE :search OR c.name LIKE :search OR q.title LIKE :search)';
            $params['search']  = '%' . $filters['search'] . '%';
        }

        $sql = 'SELECT q.id, q.number, q.title, q.status, q.total_ttc, q.issue_date, q.validity_date,
                       c.name AS client_name
                FROM quotes q
                LEFT JOIN clients c ON c.id = q.client_id
                WHERE ' . implode(' AND ', $where) .
               ' ORDER BY q.created_at DESC
                LIMIT :limit OFFSET :offset';

        $params['limit']  = $perPage;
        $params['offset'] = ($page - 1) * $perPage;

        return Database::query($sql, $params)->fetchAll();
    }

    public static function convertToInvoice(int $id, int $companyId, int $userId): int
    {
        $quote = self::findById($id, $companyId);
        if (!$quote) throw new \RuntimeException('Devis introuvable');
        if ($quote['status'] !== 'accepted') throw new \RuntimeException('Le devis doit être accepté');

        // Générer numéro facture
        $year = (int) date('Y');
        Database::beginTransaction();
        try {
            $seq = Database::query(
                'SELECT last_number FROM number_sequences WHERE company_id=? AND type=? AND year=? FOR UPDATE',
                [$companyId, 'invoice', $year]
            )->fetch();

            $next = $seq ? $seq['last_number'] + 1 : 1;
            if ($seq) {
                Database::query('UPDATE number_sequences SET last_number=? WHERE company_id=? AND type=? AND year=?',
                    [$next, $companyId, 'invoice', $year]);
            } else {
                Database::query('INSERT INTO number_sequences (company_id,type,year,last_number) VALUES (?,?,?,?)',
                    [$companyId, 'invoice', $year, $next]);
            }

            $invoiceNumber = sprintf('FAC-%d-%04d', $year, $next);

            Database::query(
                'INSERT INTO invoices (company_id,client_id,quote_id,created_by,number,status,title,issue_date,due_date,currency,subtotal_ht,total_discount,total_vat,total_ttc,notes)
                 VALUES (?,?,?,?,?,?,?,CURDATE(),DATE_ADD(CURDATE(), INTERVAL 30 DAY),?,?,?,?,?,?)',
                [
                    $companyId, $quote['client_id'], $id, $userId,
                    $invoiceNumber, 'draft', $quote['title'],
                    $quote['currency'],
                    $quote['subtotal_ht'], $quote['total_discount'],
                    $quote['total_vat'], $quote['total_ttc'],
                    $quote['notes'],
                ]
            );

            $invoiceId = (int) Database::lastInsertId();

            // Copier les lignes
            foreach ($quote['lines'] as $line) {
                Database::query(
                    'INSERT INTO invoice_lines (invoice_id,position,type,reference,name,description,unit,quantity,unit_price,vat_rate,discount_type,discount_value,total_ht,total_ttc)
                     VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                    [$invoiceId,$line['position'],$line['type'],$line['reference'],
                     $line['name'],$line['description'],$line['unit'],
                     $line['quantity'],$line['unit_price'],$line['vat_rate'],
                     $line['discount_type'],$line['discount_value'],
                     $line['total_ht'],$line['total_ttc']]
                );
            }

            // Marquer le devis comme converti
            Database::query(
                'UPDATE quotes SET status="converted", converted_to=? WHERE id=?',
                [$invoiceId, $id]
            );

            Database::commit();
            return $invoiceId;
        } catch (\Throwable $e) {
            Database::rollback();
            throw $e;
        }
    }
}
