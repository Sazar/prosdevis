<?php

use App\Services\FacturX;

/**
 * Tests unitaires basiques pour FacturX::generate()
 * Lancer : php tests/FacturXTest.php
 */

require __DIR__ . '/../vendor/autoload.php';

$invoice = [
    'id'             => 1,
    'number'         => 'FAC-2026-0001',
    'issue_date'     => '2026-07-15',
    'due_date'       => '2026-08-14',
    'currency'       => 'EUR',
    'country_vat'    => 'FR',
    'subtotal_ht'    => 1000.00,
    'total_discount' => 50.00,
    'total_vat'      => 190.00,
    'total_ttc'      => 1140.00,
    'amount_paid'    => 0.00,
    'facturx_xml'    => null,
];

$company = [
    'name'       => 'Acme SAS',
    'siret'      => '12345678901234',
    'vat_number' => 'FR12345678901',
    'address'    => '1 rue de la Paix',
    'zip'        => '75001',
    'city'       => 'Paris',
    'country'    => 'FR',
    'email'      => 'contact@acme.fr',
];

$client = [
    'name'       => 'Client SARL',
    'vat_number' => 'FR98765432109',
    'address'    => '10 avenue de Lyon',
    'zip'        => '69001',
    'city'       => 'Lyon',
    'country'    => 'FR',
    'email'      => 'compta@client.fr',
];

$lines = [
    [
        'name'           => 'Développement web',
        'description'    => 'Sprint 1 — maquettes et intégration',
        'reference'      => 'DEV-001',
        'quantity'       => 10,
        'unit'           => 'HUR',
        'unit_price'     => 95.00,
        'vat_rate'       => 20.00,
        'discount_type'  => 'percent',
        'discount_value' => 5,
        'total_ht'       => 902.50,
        'total_ttc'      => 1083.00,
    ],
    [
        'name'           => 'Hébergement annuel',
        'description'    => '',
        'reference'      => null,
        'quantity'       => 1,
        'unit'           => 'C62',
        'unit_price'     => 97.50,
        'vat_rate'       => 20.00,
        'discount_type'  => null,
        'discount_value' => 0,
        'total_ht'       => 97.50,
        'total_ttc'      => 117.00,
    ],
];

$xml = FacturX::generate($invoice, $lines, $company, $client);

$pass = 0;
$fail = 0;

$assert = function(bool $cond, string $label) use (&$pass, &$fail) {
    if ($cond) { echo "  ✓ {$label}\n"; $pass++; }
    else       { echo "  ✗ FAIL: {$label}\n"; $fail++; }
};

echo "\n=== FacturX::generate() tests ===\n";

$assert(FacturX::validate($xml),                         'XML bien formé (DOMDocument)');
$assert(str_contains($xml, 'FAC-2026-0001'),              'Numéro de facture présent');
$assert(str_contains($xml, 'Acme SAS'),                   'Nom vendeur présent');
$assert(str_contains($xml, 'Client SARL'),                'Nom acheteur présent');
$assert(str_contains($xml, '20260715'),                   'Date émission format 102');
$assert(str_contains($xml, '20260814'),                   'Date échéance format 102');
$assert(str_contains($xml, 'urn:cen.eu:en16931'),         'Profil EN 16931 déclaré');
$assert(str_contains($xml, '<ram:TypeCode>380</ram:TypeCode>'), 'TypeCode 380 (facture)');
$assert(str_contains($xml, 'Développement web'),          'Ligne 1 présente');
$assert(str_contains($xml, 'Hébergement annuel'),         'Ligne 2 présente');
$assert(str_contains($xml, '1140.00'),                    'Total TTC correct');
$assert(str_contains($xml, '190.00'),                     'Total TVA correct');
$assert(str_contains($xml, 'FR12345678901'),              'TVA vendeur présente');
$assert(str_contains($xml, 'FR98765432109'),              'TVA acheteur présente');

echo "\n{$pass} passés, {$fail} échoués\n\n";
exit($fail > 0 ? 1 : 0);
