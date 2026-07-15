<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Database;
use App\Services\FacturX;

class FacturXController
{
    /**
     * GET /invoices/{id}/facturx — télécharger le XML Factur-X d'une facture
     */
    public function download(int $id): void
    {
        Auth::require();
        $companyId = Auth::companyId();

        $invoice = Database::query(
            'SELECT i.*, co.name AS company_name, co.siret AS company_siret,
                    co.vat_number AS company_vat, co.address AS company_address,
                    co.zip AS company_zip, co.city AS company_city, co.country AS company_country,
                    co.email AS company_email
             FROM invoices i
             INNER JOIN companies co ON co.id = i.company_id
             WHERE i.id = ? AND i.company_id = ? LIMIT 1',
            [$id, $companyId]
        )->fetch();

        if (!$invoice) {
            http_response_code(404);
            echo 'Facture introuvable';
            return;
        }

        $client = Database::query(
            'SELECT * FROM clients WHERE id = ? LIMIT 1',
            [$invoice['client_id']]
        )->fetch();

        $lines = Database::query(
            'SELECT * FROM invoice_lines WHERE invoice_id = ? ORDER BY position ASC',
            [$id]
        )->fetchAll();

        $company = [
            'name'       => $invoice['company_name'],
            'siret'      => $invoice['company_siret'],
            'vat_number' => $invoice['company_vat'],
            'address'    => $invoice['company_address'],
            'zip'        => $invoice['company_zip'],
            'city'       => $invoice['company_city'],
            'country'    => $invoice['company_country'],
            'email'      => $invoice['company_email'],
        ];

        $xml = FacturX::getOrGenerate($invoice, $lines, $company, $client ?: []);

        $filename = 'factur-x_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $invoice['number']) . '.xml';

        header('Content-Type: application/xml; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($xml));
        header('Cache-Control: no-cache, no-store');
        echo $xml;
    }

    /**
     * POST /invoices/{id}/facturx/regenerate — forcer la regénération du XML
     */
    public function regenerate(int $id): void
    {
        Auth::require();
        if (!\App\Helpers\Session::verifyCsrf($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            exit;
        }

        $companyId = Auth::companyId();

        $invoice = Database::query(
            'SELECT i.*, co.name AS company_name, co.siret AS company_siret,
                    co.vat_number AS company_vat, co.address AS company_address,
                    co.zip AS company_zip, co.city AS company_city, co.country AS company_country,
                    co.email AS company_email
             FROM invoices i
             INNER JOIN companies co ON co.id = i.company_id
             WHERE i.id = ? AND i.company_id = ? LIMIT 1',
            [$id, $companyId]
        )->fetch();

        if (!$invoice) {
            http_response_code(404);
            exit;
        }

        $client = Database::query('SELECT * FROM clients WHERE id = ? LIMIT 1', [$invoice['client_id']])->fetch();
        $lines  = Database::query('SELECT * FROM invoice_lines WHERE invoice_id = ? ORDER BY position ASC', [$id])->fetchAll();

        $company = [
            'name'       => $invoice['company_name'],
            'siret'      => $invoice['company_siret'],
            'vat_number' => $invoice['company_vat'],
            'address'    => $invoice['company_address'],
            'zip'        => $invoice['company_zip'],
            'city'       => $invoice['company_city'],
            'country'    => $invoice['company_country'],
            'email'      => $invoice['company_email'],
        ];

        $xml = FacturX::generate($invoice, $lines, $company, $client ?: []);
        FacturX::saveToInvoice($id, $xml);

        Database::query(
            'INSERT INTO activity_logs (user_id, company_id, action, entity_type, entity_id, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())',
            [Auth::id(), $companyId, 'invoice.facturx_regenerated', 'invoice', $id]
        );

        \App\Helpers\Session::flash('success', 'Factur-X XML régénéré avec succès.');
        header('Location: /invoices/' . $id);
        exit;
    }
}
