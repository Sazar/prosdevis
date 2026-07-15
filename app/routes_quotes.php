<?php
// Routes devis — à inclure dans le routeur principal
// Exemple d'usage dans Router.php :
//   require __DIR__ . '/routes_quotes.php';

$router->get('/quotes',                       [QuoteController::class, 'index']);
$router->get('/quotes/new',                   [QuoteController::class, 'create']);
$router->post('/quotes',                      [QuoteController::class, 'store']);
$router->get('/quotes/{id}',                  [QuoteController::class, 'show']);
$router->get('/quotes/{id}/edit',             [QuoteController::class, 'edit']);
$router->post('/quotes/{id}',                 [QuoteController::class, 'update']);
$router->post('/quotes/{id}/status',          [QuoteController::class, 'updateStatus']);
$router->post('/quotes/{id}/send',            [QuoteController::class, 'sendEmail']);
$router->get('/quotes/{id}/duplicate',        [QuoteController::class, 'duplicate']);
$router->post('/quotes/{id}/convert',         [QuoteController::class, 'convertToInvoice']);
// $router->get('/quotes/{id}/pdf',           [QuotePdfController::class, 'generate']); // Vague 8
