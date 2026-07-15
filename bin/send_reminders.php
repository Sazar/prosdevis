#!/usr/bin/env php
<?php
/**
 * ProsDevis — Cron de relances automatiques
 *
 * Usage crontab :
 *   0 8 * * 1-5 php /var/www/prosdevis/bin/send_reminders.php >> /var/log/prosdevis_reminders.log 2>&1
 *
 * Ce script :
 *  1. Charge toutes les factures éligibles à une relance (niveaux 1, 2, 3)
 *  2. Envoie l'email de relance adapté au niveau
 *  3. Trace l'envoi dans invoice_reminders + activity_logs
 *  4. Loggue le résultat sur stdout
 */

define('ROOT', dirname(__DIR__));
require ROOT . '/vendor/autoload.php';

use App\Models\Reminder;
use App\Services\ReminderMailer;

$start = microtime(true);
$date  = date('Y-m-d H:i:s');
echo "[{$date}] ProsDevis — Démarrage relances automatiques\n";

try {
    $invoices = Reminder::getEligible();
    $count    = count($invoices);
    echo "[{$date}] {$count} facture(s) éligible(s) trouvée(s)\n";

    foreach ($invoices as $invoice) {
        $level = Reminder::nextLevel((int)$invoice['id']);
        $sent  = ReminderMailer::send($invoice, $level);

        Reminder::log(
            (int) $invoice['id'],
            (int) $invoice['company_id'],
            $level,
            $sent,
            $invoice['client_email'] ?? ''
        );

        $status = $sent ? 'OK' : 'ECHEC_MAIL';
        $balance = number_format((float)$invoice['balance_due'], 2, ',', ' ');
        echo "[{$date}] FAC #{$invoice['number']} | Client: {$invoice['client_name']} | Niveau {$level} | Retard: {$invoice['days_overdue']}j | Solde: {$balance} € | Envoi: {$status}\n";
    }

} catch (\Throwable $e) {
    echo "[{$date}] ERREUR: " . $e->getMessage() . "\n";
    exit(1);
}

$elapsed = round(microtime(true) - $start, 2);
echo "[{$date}] Terminé en {$elapsed}s\n";
exit(0);
