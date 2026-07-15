<?php
// Variables disponibles : $invoice (array avec 'lines'), $user (array)
$fmt     = fn(float $n) => number_format($n, 2, ',', '\u00a0') . '\u00a0\u20ac';
$fmtDate = fn(string $d) => date('d/m/Y', strtotime($d));
$statusLabels = [
    'draft'     => 'Brouillon',
    'sent'      => 'Envoy\u00e9e',
    'partial'   => 'Partiellement pay\u00e9e',
    'paid'      => 'Pay\u00e9e',
    'cancelled' => 'Annul\u00e9e',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 9.5pt;
    color: #1a1a1a;
    background: #fff;
    line-height: 1.45;
}
.page {
    padding: 14mm 14mm 16mm 14mm;
    width: 100%;
    min-height: 277mm;
    display: flex;
    flex-direction: column;
}
/* En-t\u00eate */
.header { display: table; width: 100%; margin-bottom: 8mm; }
.header-left, .header-right { display: table-cell; vertical-align: top; }
.header-right { text-align: right; width: 48%; }
.company-name { font-size: 18pt; font-weight: 700; color: #01696f; letter-spacing: -0.03em; margin-bottom: 3pt; }
.company-info { font-size: 8pt; color: #555; line-height: 1.6; }
.doc-title { font-size: 22pt; font-weight: 900; color: #1a1a1a; letter-spacing: -0.04em; margin-bottom: 2pt; }
.doc-meta { font-size: 8.5pt; color: #555; line-height: 1.7; }
.doc-meta strong { color: #1a1a1a; }
/* Badge statut */
.status-badge { display: inline-block; padding: 2pt 8pt; border-radius: 12pt; font-size: 7.5pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; margin-top: 4pt; }
.status-draft    { background: #f3f4f6; color: #374151; }
.status-sent     { background: #dbeafe; color: #1d4ed8; }
.status-partial  { background: #fef3c7; color: #92400e; }
.status-paid     { background: #d1fae5; color: #065f46; }
.status-cancelled{ background: #fee2e2; color: #991b1b; }
/* Dividers */
.divider { border: none; border-top: 1.5pt solid #e5e7eb; margin: 5mm 0; }
.divider-accent { border-top-color: #01696f; border-top-width: 2pt; }
/* Adresses */
.addresses { display: table; width: 100%; margin-bottom: 7mm; background: #f9fafb; border-radius: 4pt; padding: 5mm; }
.addr-block { display: table-cell; vertical-align: top; width: 50%; padding-right: 4mm; }
.addr-block:last-child { padding-right: 0; padding-left: 4mm; border-left: 1pt solid #e5e7eb; }
.addr-label { font-size: 7pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #9ca3af; margin-bottom: 3pt; }
.addr-name { font-weight: 700; font-size: 9.5pt; color: #111; margin-bottom: 2pt; }
.addr-detail { font-size: 8.5pt; color: #555; line-height: 1.6; }
/* Tableau lignes */
table.lines { width: 100%; border-collapse: collapse; margin-bottom: 6mm; font-size: 8.5pt; }
table.lines thead tr { background: #01696f; color: #fff; }
table.lines thead th { padding: 4pt 6pt; text-align: left; font-weight: 700; font-size: 7.5pt; text-transform: uppercase; letter-spacing: 0.05em; white-space: nowrap; }
table.lines thead th.num { text-align: right; }
table.lines tbody tr { border-bottom: 0.5pt solid #f0f0f0; }
table.lines tbody tr:last-child { border-bottom: none; }
table.lines tbody tr:nth-child(even) { background: #f9fafb; }
table.lines tbody td { padding: 5pt 6pt; vertical-align: top; color: #222; }
table.lines tbody td.num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
.line-desc { font-size: 7.5pt; color: #777; margin-top: 1.5pt; line-height: 1.4; }
/* Totaux */
.totals-wrapper { display: table; width: 100%; margin-bottom: 6mm; }
.totals-spacer { display: table-cell; width: 56%; }
.totals-table-wrap { display: table-cell; vertical-align: top; }
table.totals { width: 100%; border-collapse: collapse; font-size: 8.5pt; }
table.totals td { padding: 3pt 6pt; border-bottom: 0.5pt solid #f0f0f0; }
table.totals td:last-child { text-align: right; font-variant-numeric: tabular-nums; font-weight: 600; white-space: nowrap; }
table.totals tr:last-child td { border-bottom: none; }
.totals-label { color: #555; }
.totals-discount { color: #dc2626; }
.total-final td { font-size: 11pt; font-weight: 900; border-top: 2pt solid #01696f !important; border-bottom: none !important; padding-top: 5pt !important; color: #01696f; }
/* Paiement */
.payment-box { background: #f0fdf4; border: 1pt solid #bbf7d0; border-radius: 4pt; padding: 4mm; margin-bottom: 5mm; font-size: 8.5pt; }
.payment-box strong { color: #065f46; }
.payment-progress-bg { background: #d1fae5; height: 6pt; border-radius: 3pt; margin-top: 3pt; }
.payment-progress-fill { background: #16a34a; height: 6pt; border-radius: 3pt; }
/* Notes */
.notes-section { font-size: 8pt; color: #555; line-height: 1.6; margin-top: 4mm; padding-top: 4mm; border-top: 0.5pt solid #e5e7eb; }
.notes-section strong { color: #1a1a1a; font-size: 8.5pt; }
/* Footer */
.footer { margin-top: auto; padding-top: 5mm; border-top: 0.5pt solid #e5e7eb; font-size: 7.5pt; color: #9ca3af; text-align: center; line-height: 1.7; }
.footer-legal { margin-top: 2pt; font-size: 7pt; }
</style>
</head>
<body>
<div class="page">

  <!-- En-t\u00eate -->
  <div class="header">
    <div class="header-left">
      <div class="company-name"><?= htmlspecialchars($user['company_name'] ?? 'Mon Entreprise') ?></div>
      <div class="company-info">
        <?php if (!empty($user['company_address'])): ?><?= nl2br(htmlspecialchars($user['company_address'])) ?><br><?php endif; ?>
        <?php if (!empty($user['company_zip']) || !empty($user['company_city'])): ?><?= htmlspecialchars(trim(($user['company_zip'] ?? '') . ' ' . ($user['company_city'] ?? ''))) ?><br><?php endif; ?>
        <?php if (!empty($user['company_siret'])): ?>SIRET\u00a0: <?= htmlspecialchars($user['company_siret']) ?><br><?php endif; ?>
        <?php if (!empty($user['company_vat'])): ?>N\u00b0 TVA\u00a0: <?= htmlspecialchars($user['company_vat']) ?><br><?php endif; ?>
        <?php if (!empty($user['email'])): ?><?= htmlspecialchars($user['email']) ?><?php endif; ?>
      </div>
    </div>
    <div class="header-right">
      <div class="doc-title">FACTURE</div>
      <div class="doc-meta">
        N\u00b0\u00a0<strong><?= htmlspecialchars($invoice['number']) ?></strong><br>
        Date d'\u00e9mission\u00a0: <strong><?= $fmtDate($invoice['issue_date']) ?></strong><br>
        \u00c9ch\u00e9ance\u00a0: <strong><?= $fmtDate($invoice['due_date']) ?></strong><br>
        <?php if (!empty($invoice['quote_id'])): ?>Devis d'origine\u00a0: <strong><?= htmlspecialchars($invoice['quote_number'] ?? '#' . $invoice['quote_id']) ?></strong><br><?php endif; ?>
      </div>
      <span class="status-badge status-<?= htmlspecialchars($invoice['status']) ?>">
        <?= $statusLabels[$invoice['status']] ?? $invoice['status'] ?>
      </span>
    </div>
  </div>

  <hr class="divider divider-accent">

  <!-- Adresses -->
  <div class="addresses">
    <div class="addr-block">
      <div class="addr-label">\u00c9metteur</div>
      <div class="addr-name"><?= htmlspecialchars($user['company_name'] ?? '') ?></div>
      <div class="addr-detail">
        <?php if (!empty($user['company_address'])): ?><?= nl2br(htmlspecialchars($user['company_address'])) ?><br><?= htmlspecialchars(trim(($user['company_zip'] ?? '') . ' ' . ($user['company_city'] ?? ''))) ?><br><?php endif; ?>
        <?php if (!empty($user['company_siret'])): ?>SIRET\u00a0: <?= htmlspecialchars($user['company_siret']) ?><br><?php endif; ?>
        <?php if (!empty($user['company_vat'])): ?>N\u00b0 TVA\u00a0: <?= htmlspecialchars($user['company_vat']) ?><?php endif; ?>
      </div>
    </div>
    <div class="addr-block">
      <div class="addr-label">Client</div>
      <div class="addr-name"><?= htmlspecialchars($invoice['client_name']) ?></div>
      <div class="addr-detail">
        <?php if ($invoice['client_address']): ?><?= htmlspecialchars($invoice['client_address']) ?><br><?= htmlspecialchars(trim(($invoice['client_zip'] ?? '') . ' ' . ($invoice['client_city'] ?? ''))) ?><br><?php endif; ?>
        <?php if ($invoice['client_email']): ?><?= htmlspecialchars($invoice['client_email']) ?><br><?php endif; ?>
        <?php if ($invoice['client_vat']): ?>N\u00b0 TVA\u00a0: <?= htmlspecialchars($invoice['client_vat']) ?><?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Objet -->
  <?php if ($invoice['title']): ?>
  <p style="font-size:10pt; font-weight:700; color:#111; margin-bottom:3mm;">Objet\u00a0: <?= htmlspecialchars($invoice['title']) ?></p>
  <?php endif; ?>
  <?php if ($invoice['description']): ?>
  <p style="font-size:8.5pt; color:#444; margin-bottom:5mm; line-height:1.6;"><?= nl2br(htmlspecialchars($invoice['description'])) ?></p>
  <?php endif; ?>

  <!-- Tableau des lignes -->
  <table class="lines">
    <thead>
      <tr>
        <th style="width:38%;">D\u00e9signation</th>
        <th class="num" style="width:8%;">Qt\u00e9</th>
        <th class="num" style="width:12%;">PU HT</th>
        <th class="num" style="width:10%;">Remise</th>
        <th class="num" style="width:8%;">TVA</th>
        <th class="num" style="width:12%;">Total HT</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($invoice['lines'] as $line): ?>
      <tr>
        <td>
          <strong><?= htmlspecialchars($line['name']) ?></strong>
          <?php if ($line['description']): ?><div class="line-desc"><?= nl2br(htmlspecialchars($line['description'])) ?></div><?php endif; ?>
          <?php if (!empty($line['reference'])): ?><div class="line-desc">R\u00e9f.\u00a0<?= htmlspecialchars($line['reference']) ?></div><?php endif; ?>
        </td>
        <td class="num"><?= number_format((float)$line['quantity'], 2, ',', '') ?></td>
        <td class="num"><?= $fmt((float)$line['unit_price']) ?></td>
        <td class="num"><?php if ($line['discount_value'] > 0): ?><?= number_format((float)$line['discount_value'], 2, ',', '') ?><?= $line['discount_type'] === 'percent' ? '%' : '\u00a0\u20ac' ?><?php else: ?>\u2014<?php endif; ?></td>
        <td class="num"><?= number_format((float)$line['vat_rate'], 1, ',', '') ?>%</td>
        <td class="num"><strong><?= $fmt((float)$line['total_ht']) ?></strong></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Totaux -->
  <div class="totals-wrapper">
    <div class="totals-spacer"></div>
    <div class="totals-table-wrap">
      <table class="totals">
        <tr><td class="totals-label">Sous-total HT</td><td><?= $fmt((float)$invoice['subtotal_ht']) ?></td></tr>
        <?php if ($invoice['total_discount'] > 0): ?>
        <tr class="totals-discount"><td>Remise<?php if ($invoice['discount_type'] === 'percent'): ?> (<?= number_format((float)$invoice['discount_value'], 2, ',', '') ?>%)<?php endif; ?></td><td>\u2212\u00a0<?= $fmt((float)$invoice['total_discount']) ?></td></tr>
        <?php endif; ?>
        <tr><td class="totals-label">TVA</td><td><?= $fmt((float)$invoice['total_vat']) ?></td></tr>
        <tr class="total-final"><td>Total TTC</td><td><?= $fmt((float)$invoice['total_ttc']) ?></td></tr>
      </table>
    </div>
  </div>

  <!-- Suivi paiement -->
  <?php
  $paid    = (float)$invoice['amount_paid'];
  $balance = (float)$invoice['balance_due'];
  $pct     = $invoice['total_ttc'] > 0 ? min(100, round($paid / $invoice['total_ttc'] * 100)) : 0;
  ?>
  <?php if ($paid > 0 || $invoice['status'] === 'paid'): ?>
  <div class="payment-box">
    <strong>Suivi de paiement</strong><br>
    D\u00e9j\u00e0 encaiss\u00e9\u00a0: <?= $fmt($paid) ?> &nbsp;&mdash;&nbsp;
    <?php if ($balance > 0): ?>
    Solde restant d\u00fb\u00a0: <strong style="color:#dc2626;"><?= $fmt($balance) ?></strong>
    <?php else: ?>
    <strong>Facture int\u00e9gralement r\u00e9gl\u00e9e</strong>
    <?php endif; ?>
    <?php if ($invoice['paid_date']): ?> &nbsp;&mdash;&nbsp; Date de paiement\u00a0: <?= $fmtDate($invoice['paid_date']) ?><?php endif; ?>
    <div class="payment-progress-bg"><div class="payment-progress-fill" style="width:<?= $pct ?>%;"></div></div>
  </div>
  <?php elseif ($balance > 0): ?>
  <div style="text-align:right; font-size:9pt; font-weight:700; color:#dc2626; margin-bottom:5mm;">
    Solde d\u00fb\u00a0: <?= $fmt($balance) ?> &mdash; \u00c9ch\u00e9ance <?= $fmtDate($invoice['due_date']) ?>
  </div>
  <?php endif; ?>

  <!-- Conditions & notes -->
  <?php if ($invoice['payment_terms'] || $invoice['notes']): ?>
  <div class="notes-section">
    <?php if ($invoice['payment_terms']): ?><p><strong>Conditions de paiement\u00a0:</strong> <?= htmlspecialchars($invoice['payment_terms']) ?></p><?php endif; ?>
    <?php if ($invoice['notes']): ?><p style="margin-top:3pt;"><?= nl2br(htmlspecialchars($invoice['notes'])) ?></p><?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- Pied de page -->
  <div class="footer">
    <?= htmlspecialchars($user['company_name'] ?? '') ?>
    <?php if (!empty($user['company_siret'])): ?> &mdash; SIRET <?= htmlspecialchars($user['company_siret']) ?><?php endif; ?>
    <?php if (!empty($user['company_vat'])): ?> &mdash; TVA intracommunautaire <?= htmlspecialchars($user['company_vat']) ?><?php endif; ?>
    <?php if (!empty($user['company_rcs'])): ?> &mdash; RCS <?= htmlspecialchars($user['company_rcs']) ?><?php endif; ?>
    <div class="footer-legal">En cas de retard de paiement, une p\u00e9nalit\u00e9 de 3 fois le taux d'int\u00e9r\u00eat l\u00e9gal sera appliqu\u00e9e, ainsi qu'une indemnit\u00e9 forfaitaire de 40 \u20ac pour frais de recouvrement (art. L.441-10 C. com.).</div>
  </div>

</div>
</body>
</html>
