<?php
// Routes factures — à inclure dans le routeur principal

$router->get('/invoices',                [InvoiceController::class,    'index']);
$router->get('/invoices/{id}',           [InvoiceController::class,    'show']);
$router->post('/invoices/{id}/pay',      [InvoiceController::class,    'markAsPaid']);
$router->post('/invoices/{id}/remind',   [InvoiceController::class,    'sendReminder']);
$router->get('/invoices/{id}/pdf',       [InvoicePdfController::class, 'render']);
