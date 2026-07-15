<?php
// Route dashboard — à inclure dans le routeur principal
$router->get('/',          [DashboardController::class, 'index']);
$router->get('/dashboard', [DashboardController::class, 'index']);
