<?php
// Routes export comptable
$router->get('/exports',         [ExportController::class, 'index']);
$router->post('/exports/csv',    [ExportController::class, 'csv']);
$router->post('/exports/fec',    [ExportController::class, 'fec']);
