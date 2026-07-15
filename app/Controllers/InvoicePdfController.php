<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Invoice;
use Dompdf\Dompdf;
use Dompdf\Options;

class InvoicePdfController
{
    public function render(int $id): void
    {
        Auth::require();
        $companyId = Auth::companyId();

        $invoice = Invoice::findById($id, $companyId);
        if (!$invoice) {
            http_response_code(404);
            require __DIR__ . '/../Views/errors/404.php';
            return;
        }

        $user     = Auth::user();
        $download = !empty($_GET['dl']);

        $html = $this->renderTemplate($invoice, $user);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('dpi', 150);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'Facture-' . preg_replace('/[^A-Za-z0-9\-]/', '-', $invoice['number']) . '.pdf';
        $disposition = $download ? 'attachment' : 'inline';

        $dompdf->stream($filename, [
            'Attachment' => $download ? 1 : 0,
        ]);
    }

    private function renderTemplate(array $invoice, array $user): string
    {
        ob_start();
        require __DIR__ . '/../Views/invoices/pdf_template.php';
        return ob_get_clean();
    }
}
