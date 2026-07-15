<?php

namespace App\Services;

class ReminderMailer
{
    private static array $subjects = [
        1 => '[Rappel] Facture {number} — règlement en attente',
        2 => '[RELANCE] Facture {number} — solde dû : {balance}',
        3 => '[MISE EN DEMEURE] Facture {number} — action requise',
    ];

    private static array $intros = [
        1 => 'Nous vous rappelons que la facture ci-dessous arrive à échéance et n\'a pas encore été réglée.',
        2 => 'Malgré notre premier rappel, le règlement de la facture ci-dessous reste en attente. Nous vous demandons de procéder au paiement dans les meilleurs délais.',
        3 => 'Sans règlement de votre part sous 8 jours, nous nous verrons contraints d\'engager les procédures de recouvrement prévues par la loi (art. L.441-10 C. com.), y compris le versement des pénalités de retard et de l\'indemnité forfaitaire de recouvrement de 40 €.',
    ];

    public static function buildSubject(int $level, string $number, float $balance): string
    {
        return strtr(self::$subjects[$level] ?? self::$subjects[1], [
            '{number}'  => $number,
            '{balance}' => number_format($balance, 2, ',', '\u00a0') . '\u00a0€',
        ]);
    }

    public static function buildBody(int $level, array $invoice): string
    {
        $fmt = fn(float $n) => number_format($n, 2, ',', '\u00a0') . '\u00a0€';
        $intro  = self::$intros[$level] ?? self::$intros[1];
        $balance = (float)$invoice['total_ttc'] - (float)$invoice['amount_paid'];

        return implode("\r\n\r\n", [
            "Bonjour,",
            $intro,
            implode("\r\n", [
                "Facture       : " . $invoice['number'],
                "Date d'échéance : " . date('d/m/Y', strtotime($invoice['due_date'])),
                "Montant total TTC : " . $fmt((float)$invoice['total_ttc']),
                "Déjà réglé    : " . $fmt((float)$invoice['amount_paid']),
                "Solde dû      : " . $fmt($balance),
            ]),
            "Pour toute question ou si ce règlement a déjà été effectué, veuillez contacter directement " . $invoice['company_name'] . " (" . $invoice['company_email'] . ").",
            "Cordialement,\r\n" . $invoice['company_name'],
        ]);
    }

    public static function send(array $invoice, int $level): bool
    {
        $to      = $invoice['client_email'] ?? '';
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) return false;

        $balance = (float)$invoice['total_ttc'] - (float)$invoice['amount_paid'];
        $subject = self::buildSubject($level, $invoice['number'], $balance);
        $body    = self::buildBody($level, $invoice);
        $headers = implode("\r\n", [
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . $invoice['company_name'] . ' <' . $invoice['company_email'] . '>',
            'Reply-To: ' . $invoice['company_email'],
        ]);

        return @mail($to, $subject, $body, $headers);
    }
}
