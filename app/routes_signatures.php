<?php
// Routes signature électronique
$router->post('/quotes/{id}/signature/request', [SignatureController::class, 'request']);
$router->get('/sign/{token}',                   [SignatureController::class, 'show']);
$router->post('/sign/{token}',                  [SignatureController::class, 'sign']);
$router->post('/sign/{token}/decline',          [SignatureController::class, 'decline']);
