<?php
// Variables disponibles : $quote (array), $user (array)
// Rendu par QuotePdfController::renderTemplate()
$fmt = fn(float $n) => number_format($n, 2, ',', '\u00a0') . '\u00a0\u20ac';
$fmtDate = fn(string $d) => date('d/m/Y', strtotime($d));
$statusLabels = [
    'draft'     => 'Brouillon',
    'sent'      => 'Envoy\u00e9',
    'viewed'    => 'Consult\u00e9',
    'accepted'  => 'Accept\u00e9',
    'refused'   => 'Refus\u00e9',
    'expired'   => 'Expir\u00e9',
    'converted' => 'Converti',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
/* ---- Reset & base ---- */
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'DejaVu Sans', Arial, sans-serif;
    font-size: 9.5pt;
    color: #1a1a1a;
    background: #fff;
    line-height: 1.45;
}
a { color: inherit; text-decoration: none; }

/* ---- Layout page ---- */
.page {
    padding: 14mm 14mm 16mm 14mm;
    width: 100%;
    min-height: 277mm;
    display: flex;
    flex-direction: column;
}

/* ---- En-t\u00eate ---- */
.header {
    display: table;
    width: 100%;
    margin-bottom: 8mm;
}
.header-left, .header-right {
    display: table-cell;
    vertical-align: top;
}
.header-right { text-align: right; width: 48%; }

.company-name {
    font-size: 18pt;
    font-weight: 700;
    color: #01696f;
    letter-spacing: -0.03em;
    margin-bottom: 3pt;
}

.company-info {
    font-size: 8pt;
    color: #555;
    line-height: 1.6;
}

.doc-title {
    font-size: 22pt;
    font-weight: 900;
    color: #1a1a1a;
    letter-spacing: -0.04em;
    margin-bottom: 2pt;
}

.doc-meta {
    font-size: 8.5pt;
    color: #555;
    line-height: 1.7;
}

.doc-meta strong { color: #1a1a1a; }

/* ---- Statut badge ---- */
.status-badge {
    display: inline-block;
    padding: 2pt 8pt;
    border-radius: 12pt;
    font-size: 7.5pt;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin-top: 4pt;
}
.status-draft     { background: #f3f4f6; color: #374151; }
.status-sent      { background: #dbeafe; color: #1d4ed8; }
.status-accepted  { background: #d1fae5; color: #065f46; }
.status-refused   { background: #fee2e2; color: #991b1b; }
.status-converted { background: #e0e7ff; color: #3730a3; }
.status-expired   { background: #fef3c7; color: #92400e; }

/* ---- Divider ---- */
.divider {
    border: none;
    border-top: 1.5pt solid #e5e7eb;
    margin: 5mm 0;
}

.divider-accent {
    border-top-color: #01696f;
    border-top-width: 2pt;
}

/* ---- Adresses ---- */
.addresses {
    display: table;
    width: 100%;
    margin-bottom: 7mm;
    background: #f9fafb;
    border-radius: 4pt;
    padding: 5mm;
}

.addr-block { display: table-cell; vertical-align: top; width: 50%; padding-right: 4mm; }
.addr-block:last-child { padding-right: 0; padding-left: 4mm; border-left: 1pt solid #e5e7eb; }

.addr-label {
    font-size: 7pt;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #9ca3af;
    margin-bottom: 3pt;
}

.addr-name { font-weight: 700; font-size: 9.5pt; color: #111; margin-bottom: 2pt; }
.addr-detail { font-size: 8.5pt; color: #555; line-height: 1.6; }

/* ---- Description du projet ---- */
.description {
    font-size: 8.5pt;
    color: #444;
    line-height: 1.6;
    margin-bottom: 6mm;
    padding: 4mm;
    background: #fffbf0;
    border-left: 3pt solid #d19900;
    border-radius: 2pt;
}

/* ---- Tableau des lignes ---- */
table.lines {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 6mm;
    font-size: 8.5pt;
}

table.lines thead tr {
    background: #01696f;
    color: #fff;
}

table.lines thead th {
    padding: 4pt 6pt;
    text-align: left;
    font-weight: 700;
    font-size: 7.5pt;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    white-space: nowrap;
}

table.lines thead th.num { text-align: right; }

table.lines tbody tr { border-bottom: 0.5pt solid #f0f0f0; }
table.lines tbody tr:last-child { border-bottom: none; }
table.lines tbody tr:nth-child(even) { background: #f9fafb; }

table.lines tbody td {
    padding: 5pt 6pt;
    vertical-align: top;
    color: #222;
}

table.lines tbody td.num {
    text-align: right;
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
}

.line-desc { font-size: 7.5pt; color: #777; margin-top: 1.5pt; line-height: 1.4; }

/* ---- Totaux ---- */
.totals-wrapper {
    display: table;
    width: 100%;
    margin-bottom: 6mm;
}

.totals-spacer { display: table-cell; width: 56%; }

.totals-table-wrap { display: table-cell; vertical-align: top; }

table.totals {
    width: 100%;
    border-collapse: collapse;
    font-size: 8.5pt;
}

table.totals td {
    padding: 3pt 6pt;
    border-bottom: 0.5pt solid #f0f0f0;
}
table.totals td:last-child {
    text-align: right;
    font-variant-numeric: tabular-nums;
    font-weight: 600;
    white-space: nowrap;
}
table.totals tr:last-child td { border-bottom: none; }

.totals-label { color: #555; }
.totals-discount { color: #dc2626; }

.total-final td {
    font-size: 11pt;
    font-weight: 900;
    border-top: 2pt solid #01696f !important;
    border-bottom: none !important;
    padding-top: 5pt !important;
    color: #01696f;
}

.total-deposit td {
    font-size: 8pt;
    color: #01696f;
    border-top: none !important;
}

/* ---- Notes & conditions ---- */
.notes-section {
    font-size: 8pt;
    color: #555;
    line-height: 1.6;
    margin-top: 4mm;
    padding-top: 4mm;
    border-top: 0.5pt solid #e5e7eb;
}

.notes-section strong { color: #1a1a1a; font-size: 8.5pt; }

/* ---- Pied de page ---- */
.footer {
    margin-top: auto;
    padding-top: 5mm;
    border-top: 0.5pt solid #e5e7eb;
    font-size: 7.5pt;
    color: #9ca3af;
    text-align: center;
    line-height: 1.7;
}
</style>
</head>
<body>
<div class="page">

  <!-- En-t\u00eate -->
  <div class="header">
    <div class="header-left">
      <div class="company-name"><?= htmlspecialchars($user['company_name'] ?? 'Mon Entreprise') ?></div>
      <div class="company-info">
        <?php if (!empty($user['company_address'])): ?>
        <?= nl2br(htmlspecialchars($user['company_address'])) ?><br>
        <?php endif; ?>
        <?php if (!empty($user['company_zip']) || !empty($user['company_city'])): ?>
        <?= htmlspecialchars(trim(($user['company_zip'] ?? '') . ' ' . ($user['company_city'] ?? ''))) ?><br>
        <?php endif; ?>
        <?php if (!empty($user['company_siret'])): ?>SIRET : <?= htmlspecialchars($user['company_siret']) ?><br><?php endif; ?>
        <?php if (!empty($user['company_vat'])): ?>TVA : <?= htmlspecialchars($user['company_vat']) ?><br><?php endif; ?>
        <?php if (!empty($user['email'])): ?><?= htmlspecialchars($user['email']) ?><?php endif; ?>
      </div>
    </div>
    <div class="header-right">
      <div class="doc-title">DEVIS</div>
      <div class="doc-meta">
        N\u00b0 <strong><?= htmlspecialchars($quote['number']) ?></strong><br>
        Date d'\u00e9mission : <strong><?= $fmtDate($quote['issue_date']) ?></strong><br>
        Valable jusqu'au : <strong><?= $fmtDate($quote['validity_date']) ?></strong><br>
        <?php if ($quote['deposit_percent'] > 0): ?>
        Acompte : <strong><?= (int)$quote['deposit_percent'] ?>%</strong><br>
        <?php endif; ?>
      </div>
      <span class="status-badge status-<?= htmlspecialchars($quote['status']) ?>">
        <?= $statusLabels[$quote['status']] ?? $quote['status'] ?>
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
        <?php if (!empty($user['company_address'])): ?>
        <?= nl2br(htmlspecialchars($user['company_address'])) ?><br>
        <?= htmlspecialchars(trim(($user['company_zip'] ?? '') . ' ' . ($user['company_city'] ?? ''))) ?><br>
        <?php endif; ?>
        <?php if (!empty($user['company_siret'])): ?>SIRET : <?= htmlspecialchars($user['company_siret']) ?><br><?php endif; ?>
        <?php if (!empty($user['company_vat'])): ?>N\u00b0 TVA : <?= htmlspecialchars($user['company_vat']) ?><?php endif; ?>
      </div>
    </div>
    <div class="addr-block">
      <div class="addr-label">Client</div>
      <div class="addr-name"><?= htmlspecialchars($quote['client_name']) ?></div>
      <div class="addr-detail">
        <?php if ($quote['client_address']): ?>
        <?= htmlspecialchars($quote['client_address']) ?><br>
        <?= htmlspecialchars(trim(($quote['client_zip'] ?? '') . ' ' . ($quote['client_city'] ?? ''))) ?><br>
        <?php endif; ?>
        <?php if ($quote['client_email']): ?><?= htmlspecialchars($quote['client_email']) ?><br><?php endif; ?>
        <?php if ($quote['client_vat']): ?>N\u00b0 TVA : <?= htmlspecialchars($quote['client_vat']) ?><?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Objet -->
  <?php if ($quote['title']): ?>
  <p style="font-size:10pt; font-weight:700; color:#111; margin-bottom:3mm;">Objet : <?= htmlspecialchars($quote['title']) ?></p>
  <?php endif; ?>

  <!-- Description -->
  <?php if ($quote['description']): ?>
  <div class="description"><?= nl2br(htmlspecialchars($quote['description'])) ?></div>
  <?php endif; ?>

  <!-- Lignes de prestation -->
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
      <?php foreach ($quote['lines'] as $line): ?>
      <tr>
        <td>
          <strong><?= htmlspecialchars($line['name']) ?></strong>
          <?php if ($line['description']): ?>
          <div class="line-desc"><?= nl2br(htmlspecialchars($line['description'])) ?></div>
          <?php endif; ?>
          <?php if ($line['reference']): ?>
          <div class="line-desc">R\u00e9f. <?= htmlspecialchars($line['reference']) ?></div>
          <?php endif; ?>
        </td>
        <td class="num"><?= number_format((float)$line['quantity'], 2, ',', '') ?></td>
        <td class="num"><?= $fmt((float)$line['unit_price']) ?></td>
        <td class="num">
          <?php if ($line['discount_value'] > 0): ?>
          <?= number_format((float)$line['discount_value'], 2, ',', '') ?><?= $line['discount_type'] === 'percent' ? '%' : '\u00a0\u20ac' ?>
          <?php else: ?>\u2014<?php endif; ?>
        </td>
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
        <tr>
          <td class="totals-label">Sous-total HT</td>
          <td><?= $fmt((float)$quote['subtotal_ht']) ?></td>
        </tr>
        <?php if ($quote['total_discount'] > 0): ?>
        <tr class="totals-discount">
          <td>Remise
            <?php if ($quote['discount_type'] === 'percent'): ?>
            (<?= number_format((float)$quote['discount_value'], 2, ',', '') ?>%)
            <?php endif; ?>
          </td>
          <td>\u2212\u00a0<?= $fmt((float)$quote['total_discount']) ?></td>
        </tr>
        <?php endif; ?>
        <tr>
          <td class="totals-label">TVA</td>
          <td><?= $fmt((float)$quote['total_vat']) ?></td>
        </tr>
        <tr class="total-final">
          <td>Total TTC</td>
          <td><?= $fmt((float)$quote['total_ttc']) ?></td>
        </tr>
        <?php if ($quote['deposit_percent'] > 0): ?>
        <tr class="total-deposit">
          <td>Acompte (<?= (int)$quote['deposit_percent'] ?>%)</td>
          <td><?= $fmt($quote['total_ttc'] * $quote['deposit_percent'] / 100) ?></td>
        </tr>
        <?php endif; ?>
      </table>
    </div>
  </div>

  <!-- Notes & conditions -->
  <?php if ($quote['payment_terms'] || $quote['notes']): ?>
  <div class="notes-section">
    <?php if ($quote['payment_terms']): ?>
    <p><strong>Conditions de paiement :</strong> <?= htmlspecialchars($quote['payment_terms']) ?></p>
    <?php endif; ?>
    <?php if ($quote['notes']): ?>
    <p style="margin-top:3pt;"><?= nl2br(htmlspecialchars($quote['notes'])) ?></p>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- Pied de page -->
  <div class="footer">
    <?= htmlspecialchars($user['company_name'] ?? '') ?>
    <?php if (!empty($user['company_siret'])): ?> &mdash; SIRET <?= htmlspecialchars($user['company_siret']) ?><?php endif; ?>
    <?php if (!empty($user['company_vat'])): ?> &mdash; TVA <?= htmlspecialchars($user['company_vat']) ?><?php endif; ?>
    <?php if (!empty($user['company_rcs'])): ?> &mdash; RCS <?= htmlspecialchars($user['company_rcs']) ?><?php endif; ?>
    <br>Ce devis est valable jusqu'au <?= $fmtDate($quote['validity_date']) ?>. Hors taxes mention\u00e9es, les prix s'entendent en euros.
  </div>

</div>
</body>
</html>
