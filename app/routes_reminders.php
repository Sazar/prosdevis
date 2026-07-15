<?php
// Routes relances factures
$router->get('/invoices/{id}/reminders',       [ReminderController::class, 'history']);
$router->post('/invoices/{id}/reminders/send', [ReminderController::class, 'send']);
