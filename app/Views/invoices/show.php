<?php
$csrf = App\Helpers\Session::csrf();
$statuses = [
    'draft'     => ['label' => 'Brouillon', 'class' => 'badge-neutral'],
    'sent'      => ['label' => 'Envoyée', 'class' => 'badge-info'],
    'partial'   => ['label' => 'Partiellement payée', 'class' => 'badge-warning'],
    'paid'      => ['label' => 'Payée', 'class' => 'badge-success'],
    'cancelled' => ['label' => 'Annulée', 'class' => 'badge-danger'],
];
$status = $statuses[$invoice['status']] ?? ['label' => $invoice['status'], 'class' => 'badge-neutral'];
$isOverdue = $invoice['due_date'] < date('Y-m-d') && !in_array($invoice['status'], ['paid','cancelled']);
$paymentProgress = $invoice['total_ttc'] > 0 ? min(100, round(($invoice['amount_paid'] / $invoice['total_ttc']) * 100)) : 0;
?>
<style>
.invoice-detail-grid { display:grid; grid-template-columns:1fr 320px; gap:var(--space-6); align-items:start; }
.invoice-preview { background:#fff; border-radius:var(--radius-xl); box-shadow:var(--shadow-lg); padding:var(--space-10) var(--space-12); color:#1a1a1a; }
[data-theme="dark"] .invoice-preview { background:#1e1e1e; color:#e8e8e8; }
.preview-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:var(--space-8); gap:var(--space-4); }
.preview-logo { font-size:1.5rem; font-weight:900; letter-spacing:-0.04em; color:var(--color-primary); }
.preview-meta { text-align:right; font-size:.85rem; line-height:1.6; color:#555; }
[data-theme="dark"] .preview-meta { color:#aaa; }
.preview-table { width:100%; border-collapse:collapse; font-size:.875rem; margin-bottom:var(--space-6); }
.preview-table thead th { background:#f3f4f6; padding:.6rem .75rem; text-align:left; font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; color:#666; font-weight:700; }
[data-theme="dark"] .preview-table thead th { background:#2c2c2c; color:#999; }
.preview-table tbody td { padding:.75rem; border-bottom:1px solid #f0f0f0; vertical-align:top; }
[data-theme="dark"] .preview-table tbody td { border-bottom-color:#2e2e2e; }
.preview-table .num { text-align:right; font-variant-numeric:tabular-nums; }
.actions-card { background:var(--color-surface); border:1px solid var(--color-border); border-radius:var(--radius-xl); overflow:hidden; position:sticky; top:calc(var(--topbar-height) + var(--space-4)); }
.actions-card-section { padding:var(--space-4) var(--space-5); border-bottom:1px solid var(--color-border); }
.actions-card-section:last-child { border-bottom:none; }
.progress-wrap { margin-top:var(--space-3); }
.progress-bar { width:100%; height:10px; background:var(--color-surface-offset); border-radius:999px; overflow:hidden; }
.progress-bar > span { display:block; height:100%; background:linear-gradient(90deg, #16a34a, #22c55e); width:<?= $paymentProgress ?>%; }
.stat-mini { display:flex; justify-content:space-between; padding:var(--space-2) 0; font-size:var(--text-sm); }
.stat-mini .label { color:var(--color-text-muted); }
.stat-mini .value { font-weight:700; font-variant-numeric:tabular-nums; }
@media (max-width:1024px){ .invoice-detail-grid{grid-template-columns:1fr;} .actions-card{position:static;} .invoice-preview{padding:var(--space-6);} }
</style>

<div class="card" style="margin-bottom:var(--space-5);">
  <div class="card-header">
    <div style="display:flex; align-items:center; gap:var(--space-4);">
      <div>
        <h2 style="font-size:var(--text-xl); font-weight:800;"><?= htmlspecialchars($invoice['number']) ?></h2>
        <p style="color:var(--color-text-muted); font-size:var(--text-sm);"><?= htmlspecialchars($invoice['title']) ?></p>
      </div>
      <span class="badge <?= $status['class'] ?>"><?= $status['label'] ?></span>
      <?php if ($isOverdue): ?><span class="badge badge-danger">⚠ En retard</span><?php endif; ?>
    </div>
    <div style="display:flex; gap:var(--space-2);">
      <a href="/invoices/<?= $invoice['id'] ?>/pdf" target="_blank" class="btn btn-secondary btn-sm">PDF</a>
    </div>
  </div>
</div>

<div class="invoice-detail-grid">
  <div>
    <div class="invoice-preview">
      <div class="preview-header">
        <div>
          <div class="preview-logo"><?= htmlspecialchars($user['company_name'] ?? 'Mon Entreprise') ?></div>
          <?php if (!empty($user['company_address'])): ?>
          <div style="font-size:.82rem; color:#666; margin-top:6px; line-height:1.5;"><?= nl2br(htmlspecialchars($user['company_address'])) ?></div>
          <?php endif; ?>
        </div>
        <div class="preview-meta">
          <strong>FACTURE <?= htmlspecialchars($invoice['number']) ?></strong><br>
          Date : <?= date('d/m/Y', strtotime($invoice['issue_date'])) ?><br>
          Échéance : <strong><?= date('d/m/Y', strtotime($invoice['due_date'])) ?></strong><br>
          Solde dû : <strong><?= number_format($invoice['balance_due'], 2, ',', '&nbsp;') ?> €</strong>
        </div>
      </div>

      <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-6); margin-bottom:var(--space-8); padding:var(--space-5); background:#f9f9f9; border-radius:8px; font-size:.875rem;">
        <div>
          <h4 style="font-size:.7rem; text-transform:uppercase; letter-spacing:.08em; color:#999; margin-bottom:.5rem;">Émetteur</h4>
          <strong><?= htmlspecialchars($user['company_name'] ?? '') ?></strong><br>
          <?= htmlspecialchars($user['company_siret'] ?? '') ?><br>
          <?= htmlspecialchars($user['company_vat'] ?? '') ?>
        </div>
        <div>
          <h4 style="font-size:.7rem; text-transform:uppercase; letter-spacing:.08em; color:#999; margin-bottom:.5rem;">Client</h4>
          <strong><?= htmlspecialchars($invoice['client_name']) ?></strong><br>
          <?php if ($invoice['client_address']): ?>
          <?= htmlspecialchars($invoice['client_address']) ?><br>
          <?= htmlspecialchars(trim($invoice['client_zip'] . ' ' . $invoice['client_city'])) ?><br>
          <?php endif; ?>
          <?php if ($invoice['client_vat']): ?>TVA : <?= htmlspecialchars($invoice['client_vat']) ?><?php endif; ?>
        </div>
      </div>

      <table class="preview-table">
        <thead>
          <tr>
            <th>Désignation</th>
            <th class="num">Qté</th>
            <th class="num">PU HT</th>
            <th class="num">TVA</th>
            <th class="num">Total HT</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($invoice['lines'] as $line): ?>
          <tr>
            <td><strong><?= htmlspecialchars($line['name']) ?></strong><?php if ($line['description']): ?><br><span style="font-size:.8rem; color:#777;"><?= nl2br(htmlspecialchars($line['description'])) ?></span><?php endif; ?></td>
            <td class="num"><?= number_format($line['quantity'], 2, ',', '') ?></td>
            <td class="num"><?= number_format($line['unit_price'], 2, ',', '&nbsp;') ?> €</td>
            <td class="num"><?= number_format($line['vat_rate'], 1, ',', '') ?>%</td>
            <td class="num"><strong><?= number_format($line['total_ht'], 2, ',', '&nbsp;') ?> €</strong></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div style="display:flex; justify-content:flex-end;">
        <div style="width:280px; font-size:.875rem;">
          <table style="width:100%;">
            <tr><td style="padding:.4rem .5rem; color:#666;">Sous-total HT</td><td style="padding:.4rem .5rem; text-align:right;"><?= number_format($invoice['subtotal_ht'], 2, ',', '&nbsp;') ?> €</td></tr>
            <?php if ($invoice['total_discount'] > 0): ?><tr><td style="padding:.4rem .5rem; color:#dc2626;">Remise</td><td style="padding:.4rem .5rem; text-align:right; color:#dc2626;">- <?= number_format($invoice['total_discount'], 2, ',', '&nbsp;') ?> €</td></tr><?php endif; ?>
            <tr><td style="padding:.4rem .5rem; color:#666;">TVA</td><td style="padding:.4rem .5rem; text-align:right;"><?= number_format($invoice['total_vat'], 2, ',', '&nbsp;') ?> €</td></tr>
            <tr><td style="padding:.65rem .5rem; border-top:2px solid #e5e7eb; font-weight:800; font-size:1.1rem;">Total TTC</td><td style="padding:.65rem .5rem; border-top:2px solid #e5e7eb; text-align:right; font-weight:800; font-size:1.1rem;"><?= number_format($invoice['total_ttc'], 2, ',', '&nbsp;') ?> €</td></tr>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div>
    <div class="actions-card">
      <div class="actions-card-section">
        <p style="font-size:var(--text-xs); font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--color-text-muted); margin-bottom:var(--space-2);">Paiement</p>
        <div class="stat-mini"><span class="label">Montant total</span><span class="value"><?= number_format($invoice['total_ttc'], 2, ',', '&nbsp;') ?> €</span></div>
        <div class="stat-mini"><span class="label">Déjà payé</span><span class="value" style="color:#16a34a;"><?= number_format($invoice['amount_paid'], 2, ',', '&nbsp;') ?> €</span></div>
        <div class="stat-mini"><span class="label">Solde dû</span><span class="value" style="color:<?= $invoice['balance_due'] > 0 ? '#dc2626' : '#16a34a' ?>;"><?= number_format($invoice['balance_due'], 2, ',', '&nbsp;') ?> €</span></div>
        <div class="progress-wrap">
          <div class="progress-bar"><span></span></div>
          <div style="margin-top:6px; font-size:var(--text-xs); color:var(--color-text-muted);"><?= $paymentProgress ?>% encaissé</div>
        </div>
      </div>

      <?php if ($invoice['balance_due'] > 0): ?>
      <div class="actions-card-section">
        <p style="font-size:var(--text-xs); font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--color-text-muted); margin-bottom:var(--space-3);">Enregistrer un paiement</p>
        <form method="POST" action="/invoices/<?= $invoice['id'] ?>/pay" style="display:grid; gap:var(--space-3);">
          <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
          <div class="form-group" style="margin:0;">
            <label>Montant encaissé</label>
            <input type="number" step="0.01" min="0.01" max="<?= htmlspecialchars($invoice['balance_due']) ?>" name="amount" class="form-control" value="<?= htmlspecialchars($invoice['balance_due']) ?>" required>
          </div>
          <div class="form-group" style="margin:0;">
            <label>Date de paiement</label>
            <input type="date" name="paid_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>
          <button type="submit" class="btn btn-success w-full">Marquer comme payé</button>
        </form>
      </div>
      <?php endif; ?>

      <?php if ($isOverdue && $invoice['client_email']): ?>
      <div class="actions-card-section">
        <p style="font-size:var(--text-xs); font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--color-text-muted); margin-bottom:var(--space-3);">Relance</p>
        <form method="POST" action="/invoices/<?= $invoice['id'] ?>/remind" style="display:grid; gap:var(--space-3);">
          <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
          <div class="form-group" style="margin:0;">
            <label>Email du client</label>
            <input type="email" name="to" class="form-control" value="<?= htmlspecialchars($invoice['client_email']) ?>" required>
          </div>
          <button type="submit" class="btn btn-primary w-full">Envoyer une relance</button>
        </form>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
