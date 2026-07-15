#!/bin/bash
# Installer Dompdf via Composer
# Exécuter une fois depuis la racine du projet :
#   bash composer_require_dompdf.sh

composer require dompdf/dompdf:^2.0

echo ""
echo "✅  Dompdf installé. Ajoutez la route PDF dans votre routeur :"
echo "   \$router->get('/quotes/{id}/pdf', [QuotePdfController::class, 'generate']);"
