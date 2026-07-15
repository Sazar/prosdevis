<?php
// Routes Factur-X
$router->get('/invoices/{id}/facturx',            [FacturXController::class, 'download']);
$router->post('/invoices/{id}/facturx/regenerate', [FacturXController::class, 'regenerate']);
