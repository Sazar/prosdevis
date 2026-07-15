<?php

namespace App\Controllers;

use App\Helpers\Auth;
use App\Models\Quote;
use Dompdf\Dompdf;
use Dompdf\Options;

class QuotePdfController
{
    /**
     * Génère le PDF d'un devis.
     * GET /quotes/{id}/pdf           → affichage inline (preview navigateur)
     * GET /quotes/{id}/pdf?dl=1      → téléchargement forcé
     */
    public function generate(int $id): void
    {
        Auth::require();

        $companyId = Auth::companyId();
        $quote     = Quote::findById($id, $companyId);

        if (!$quote) {
            http_response_code(404);
            echo 'Devis introuvable.';
            return;
        }

        $user    = Auth::user();
        $html    = $this->renderTemplate($quote, $user);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);   // sécurité : pas de ressources externes
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isFontSubsettingEnabled', true);
        $options->set('dpi', 150);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename   = 'Devis-' . $quote['number'] . '.pdf';
        $disposition = isset($_GET['dl']) ? 'attachment' : 'inline';

        header('Content-Type: application/pdf');
        header('Content-Disposition: ' . $disposition . '; filename="' . $filename . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        echo $dompdf->output();
        exit;
    }

    // -------------------------------------------------------------------------
    // Template HTML → PDF
    // -------------------------------------------------------------------------

    private function renderTemplate(array $quote, array $user): string
    {
        ob_start();
        require __DIR__ . '/../Views/quotes/pdf_template.php';
        return ob_get_clean();
    }
}
