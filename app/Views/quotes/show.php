<?php
// Vue: détail d'un devis
$csrf       = App\Helpers\Session::csrf();
$statusLabels = [
    'draft'     => ['label' => 'Brouillon',  'class' => 'badge-neutral'],
    'sent'      => ['label' => 'Envoyé',     'class' => 'badge-info'],
    'viewed'    => ['label' => 'Consulté',   'class' => 'badge-info'],
    'accepted'  => ['label' => 'Accepté',    'class' => 'badge-success'],
    'refused'   => ['label' => 'Refusé',     'class' => 'badge-danger'],
    'expired'   => ['label' => 'Expiré',     'class' => 'badge-warning'],
    'converted' => ['label' => 'Converti',   'class' => 'badge-primary'],
];
$status = $statusLabels[$quote['status']] ?? ['label' => $quote['status'], 'class' => 'badge-neutral'];
$canEdit    = !in_array($quote['status'], ['converted', 'refused']);
$canConvert = $quote['status'] === 'accepted';
$canSend    = in_array($quote['status'], ['draft', 'sent']);
$isExpired  = $quote['validity_date'] < date('Y-m-d') && !in_array($quote['status'], ['accepted','converted','refused']);
?>
<style>
.quote-detail-grid {
  display: grid;
  grid-template-columns: 1fr 320px;
  gap: var(--space-6);
  align-items: start;
}

.quote-preview {
  background: #fff;
  border-radius: var(--radius-xl);
  box-shadow: var(--shadow-lg);
  padding: var(--space-10) var(--space-12);
  color: #1a1a1a;
  font-family: 'Inter', sans-serif;
  position: relative;
}

[data-theme="dark"] .quote-preview {
  background: #1e1e1e;
  color: #e8e8e8;
}

.qp-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: var(--space-8);
  gap: var(--space-4);
}

.qp-logo {
  font-size: 1.5rem;
  font-weight: 900;
  letter-spacing: -0.04em;
  color: var(--color-primary);
}

.qp-meta {
  text-align: right;
  font-size: 0.85rem;
  line-height: 1.6;
  color: #555;
}

[data-theme="dark"] .qp-meta { color: #aaa; }

.qp-number {
  font-size: 1.4rem;
  font-weight: 800;
  color: #1a1a1a;
  margin-bottom: 0.25rem;
}

[data-theme="dark"] .qp-number { color: #e8e8e8; }

.qp-addresses {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--space-6);
  margin-bottom: var(--space-8);
  padding: var(--space-5);
  background: #f9f9f9;
  border-radius: 8px;
  font-size: 0.875rem;
}

[data-theme="dark"] .qp-addresses { background: #2a2a2a; }

.qp-addresses h4 {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #999;
  margin-bottom: 0.5rem;
  font-weight: 700;
}

.qp-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.875rem;
  margin-bottom: var(--space-6);
}

.qp-table thead th {
  background: #f3f4f6;
  padding: 0.6rem 0.75rem;
  text-align: left;
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: #666;
  font-weight: 700;
}

[data-theme="dark"] .qp-table thead th { background: #2c2c2c; color: #999; }

.qp-table tbody td {
  padding: 0.75rem;
  border-bottom: 1px solid #f0f0f0;
  vertical-align: top;
}

[data-theme="dark"] .qp-table tbody td { border-bottom-color: #2e2e2e; }

.qp-table tbody tr:last-child td { border-bottom: none; }

.qp-table .num { text-align: right; font-variant-numeric: tabular-nums; }

.qp-totals {
  margin-left: auto;
  width: 280px;
  font-size: 0.875rem;
}

.qp-totals table { width: 100%; }
.qp-totals td { padding: 0.4rem 0.5rem; }
.qp-totals td:last-child { text-align: right; font-variant-numeric: tabular-nums; }

.qp-total-final td {
  font-weight: 800;
  font-size: 1.1rem;
  border-top: 2px solid #e5e7eb;
  padding-top: 0.75rem;
}

[data-theme="dark"] .qp-total-final td { border-top-color: #3a3a3a; }

.qp-notes {
  margin-top: var(--space-8);
  padding-top: var(--space-6);
  border-top: 1px solid #e5e7eb;
  font-size: 0.8rem;
  color: #666;
  line-height: 1.6;
}

[data-theme="dark"] .qp-notes { border-top-color: #333; color: #aaa; }

/* Timeline */
.timeline {
  display: flex;
  flex-direction: column;
  gap: 0;
}

.timeline-item {
  display: flex;
  gap: var(--space-3);
  align-items: flex-start;
  position: relative;
  padding-bottom: var(--space-4);
}

.timeline-item::before {
  content: '';
  position: absolute;
  left: 11px;
  top: 24px;
  bottom: 0;
  width: 2px;
  background: var(--color-border);
}

.timeline-item:last-child::before { display: none; }

.timeline-dot {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 11px;
  font-weight: 700;
  margin-top: 2px;
}

.timeline-dot.done    { background: var(--color-primary); color: #fff; }
.timeline-dot.current { background: var(--color-warning); color: #fff; }
.timeline-dot.pending { background: var(--color-surface-offset); color: var(--color-text-muted); border: 2px solid var(--color-border); }

.timeline-text {
  font-size: var(--text-sm);
  line-height: 1.4;
}

.timeline-text strong { display: block; font-weight: 600; color: var(--color-text); }
.timeline-text span   { color: var(--color-text-muted); font-size: var(--text-xs); }

/* Actions sidebar */
.actions-card {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-xl);
  overflow: hidden;
  position: sticky;
  top: calc(var(--topbar-height) + var(--space-4));
}

.actions-card-section {
  padding: var(--space-4) var(--space-5);
  border-bottom: 1px solid var(--color-border);
}

.actions-card-section:last-child { border-bottom: none; }

.stat-mini {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--space-2) 0;
  font-size: var(--text-sm);
}

.stat-mini .label { color: var(--color-text-muted); }
.stat-mini .value { font-weight: 600; font-variant-numeric: tabular-nums; }

@media (max-width: 1024px) {
  .quote-detail-grid { grid-template-columns: 1fr; }
  .actions-card { position: static; }
  .quote-preview { padding: var(--space-6); }
  .qp-addresses { grid-template-columns: 1fr; }
  .qp-totals { width: 100%; }
}
</style>

<!-- En-tête -->
<div class="card" style="margin-bottom:var(--space-5);">
  <div class="card-header">
    <div style="display:flex; align-items:center; gap:var(--space-4);">
      <div>
        <h2 style="font-size:var(--text-xl); font-weight:800; margin-bottom:2px;"><?= htmlspecialchars($quote['number']) ?></h2>
        <p style="color:var(--color-text-muted); font-size:var(--text-sm);"><?= htmlspecialchars($quote['title']) ?></p>
      </div>
      <span class="badge <?= $status['class'] ?>" style="font-size:var(--text-sm); padding:0.35rem 0.9rem;"><?= $status['label'] ?></span>
      <?php if ($isExpired && $quote['status'] === 'sent'): ?>
      <span class="badge badge-warning" style="font-size:var(--text-xs);">⚠ Expiré</span>
      <?php endif; ?>
    </div>
    <div style="display:flex; gap:var(--space-2);">
      <?php if ($canEdit): ?>
      <a href="/quotes/<?= $quote['id'] ?>/edit" class="btn btn-secondary btn-sm">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        Modifier
      </a>
      <?php endif; ?>
      <a href="/quotes/<?= $quote['id'] ?>/pdf" target="_blank" class="btn btn-secondary btn-sm">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        PDF
      </a>
    </div>
  </div>
</div>

<div class="quote-detail-grid">

  <!-- Aperçu devis (colonne principale) -->
  <div>
    <div class="quote-preview" id="quotePrintArea">

      <div class="qp-header">
        <div>
          <div class="qp-logo"><?= htmlspecialchars($user['company_name'] ?? 'Mon Entreprise') ?></div>
          <?php if (!empty($user['company_address'])): ?>
          <div style="font-size:0.82rem; color:#666; margin-top:6px; line-height:1.5;">
            <?= nl2br(htmlspecialchars($user['company_address'])) ?>
          </div>
          <?php endif; ?>
        </div>
        <div>
          <div class="qp-number">DEVIS <?= htmlspecialchars($quote['number']) ?></div>
          <div class="qp-meta">
            Date : <?= date('d/m/Y', strtotime($quote['issue_date'])) ?><br>
            Valable jusqu'au : <strong><?= date('d/m/Y', strtotime($quote['validity_date'])) ?></strong><br>
            <?php if ($quote['deposit_percent'] > 0): ?>
            Acompte demandé : <?= (int)$quote['deposit_percent'] ?>%
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="qp-addresses">
        <div>
          <h4>Émetteur</h4>
          <strong><?= htmlspecialchars($user['company_name'] ?? '') ?></strong><br>
          <?= htmlspecialchars($user['company_siret'] ?? '') ?><br>
          <?= htmlspecialchars($user['company_vat'] ?? '') ?>
        </div>
        <div>
          <h4>Client</h4>
          <strong><?= htmlspecialchars($quote['client_name']) ?></strong><br>
          <?php if ($quote['client_address']): ?>
          <?= htmlspecialchars($quote['client_address']) ?><br>
          <?= htmlspecialchars(trim($quote['client_zip'] . ' ' . $quote['client_city'])) ?><br>
          <?php endif; ?>
          <?php if ($quote['client_vat']): ?>
          TVA : <?= htmlspecialchars($quote['client_vat']) ?>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($quote['description']): ?>
      <p style="font-size:0.875rem; color:#555; margin-bottom:var(--space-6); line-height:1.6;">
        <?= nl2br(htmlspecialchars($quote['description'])) ?>
      </p>
      <?php endif; ?>

      <table class="qp-table">
        <thead>
          <tr>
            <th style="width:40%;">Désignation</th>
            <th class="num">Qté</th>
            <th class="num">PU HT</th>
            <th class="num">Remise</th>
            <th class="num">TVA</th>
            <th class="num">Total HT</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($quote['lines'] as $line): ?>
          <tr>
            <td>
              <strong><?= htmlspecialchars($line['name']) ?></strong>
              <?php if ($line['description']): ?>
              <br><span style="font-size:0.8rem; color:#777;"><?= nl2br(htmlspecialchars($line['description'])) ?></span>
              <?php endif; ?>
            </td>
            <td class="num"><?= number_format($line['quantity'], 2, ',', '') ?></td>
            <td class="num"><?= number_format($line['unit_price'], 2, ',', '&nbsp;') ?> €</td>
            <td class="num">
              <?php if ($line['discount_value'] > 0): ?>
              <?= number_format($line['discount_value'], 2, ',', '') ?><?= $line['discount_type'] === 'percent' ? '%' : ' €' ?>
              <?php else: ?>—<?php endif; ?>
            </td>
            <td class="num"><?= number_format($line['vat_rate'], 1, ',', '') ?>%</td>
            <td class="num"><strong><?= number_format($line['total_ht'], 2, ',', '&nbsp;') ?> €</strong></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <div style="display:flex; justify-content:flex-end;">
        <div class="qp-totals">
          <table>
            <tr>
              <td style="color:#666;">Sous-total HT</td>
              <td><?= number_format($quote['subtotal_ht'], 2, ',', '&nbsp;') ?> €</td>
            </tr>
            <?php if ($quote['total_discount'] > 0): ?>
            <tr>
              <td style="color:#e53e3e;">Remise</td>
              <td style="color:#e53e3e;">- <?= number_format($quote['total_discount'], 2, ',', '&nbsp;') ?> €</td>
            </tr>
            <?php endif; ?>
            <tr>
              <td style="color:#666;">TVA</td>
              <td><?= number_format($quote['total_vat'], 2, ',', '&nbsp;') ?> €</td>
            </tr>
            <tr class="qp-total-final">
              <td>Total TTC</td>
              <td><?= number_format($quote['total_ttc'], 2, ',', '&nbsp;') ?> €</td>
            </tr>
            <?php if ($quote['deposit_percent'] > 0): ?>
            <tr>
              <td style="color:var(--color-primary);">Acompte (<?= (int)$quote['deposit_percent'] ?>%)</td>
              <td style="color:var(--color-primary);"><?= number_format($quote['total_ttc'] * $quote['deposit_percent'] / 100, 2, ',', '&nbsp;') ?> €</td>
            </tr>
            <?php endif; ?>
          </table>
        </div>
      </div>

      <?php if ($quote['notes'] || $quote['payment_terms']): ?>
      <div class="qp-notes">
        <?php if ($quote['payment_terms']): ?>
        <p><strong>Conditions de paiement :</strong> <?= htmlspecialchars($quote['payment_terms']) ?></p>
        <?php endif; ?>
        <?php if ($quote['notes']): ?>
        <p style="margin-top:8px;"><?= nl2br(htmlspecialchars($quote['notes'])) ?></p>
        <?php endif; ?>
      </div>
      <?php endif; ?>

    </div><!-- /quote-preview -->
  </div>

  <!-- Sidebar actions -->
  <div>
    <div class="actions-card">

      <!-- Chiffres clés -->
      <div class="actions-card-section">
        <p style="font-size:var(--text-xs); font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--color-text-muted); margin-bottom:var(--space-3);">Montants</p>
        <div class="stat-mini"><span class="label">HT</span><span class="value"><?= number_format($quote['subtotal_ht'], 2, ',', '&nbsp;') ?> €</span></div>
        <div class="stat-mini"><span class="label">TVA</span><span class="value"><?= number_format($quote['total_vat'], 2, ',', '&nbsp;') ?> €</span></div>
        <div class="stat-mini" style="border-top:1px solid var(--color-border); padding-top:var(--space-2); margin-top:var(--space-1);">
          <span class="label" style="font-weight:700; color:var(--color-text);">TTC</span>
          <span class="value" style="font-size:var(--text-lg); color:var(--color-primary);"><?= number_format($quote['total_ttc'], 2, ',', '&nbsp;') ?> €</span>
        </div>
      </div>

      <!-- Actions principales -->
      <div class="actions-card-section">
        <p style="font-size:var(--text-xs); font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--color-text-muted); margin-bottom:var(--space-3);">Actions</p>
        <div style="display:flex; flex-direction:column; gap:var(--space-2);">

          <?php if ($canSend): ?>
          <button type="button" onclick="openSendModal()" class="btn btn-primary w-full">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Envoyer par email
          </button>
          <?php endif; ?>

          <?php if ($canConvert): ?>
          <form method="POST" action="/quotes/<?= $quote['id'] ?>/convert" onsubmit="return confirm('Convertir ce devis en facture ?')">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <button type="submit" class="btn btn-success w-full">
              <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
              Créer la facture
            </button>
          </form>
          <?php endif; ?>

          <?php if (in_array($quote['status'], ['sent','viewed'])): ?>
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-2);">
            <form method="POST" action="/quotes/<?= $quote['id'] ?>/status">
              <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
              <input type="hidden" name="status" value="accepted">
              <button type="submit" class="btn btn-success w-full btn-sm">✓ Accepté</button>
            </form>
            <form method="POST" action="/quotes/<?= $quote['id'] ?>/status">
              <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
              <input type="hidden" name="status" value="refused">
              <button type="submit" class="btn btn-danger w-full btn-sm">✗ Refusé</button>
            </form>
          </div>
          <?php endif; ?>

          <a href="/quotes/<?= $quote['id'] ?>/pdf" target="_blank" class="btn btn-secondary w-full btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Télécharger PDF
          </a>

          <?php if (in_array($quote['status'], ['draft'])): ?>
          <a href="/quotes/<?= $quote['id'] ?>/duplicate" class="btn btn-ghost w-full btn-sm">Dupliquer</a>
          <?php endif; ?>

        </div>
      </div>

      <!-- Timeline statuts -->
      <div class="actions-card-section">
        <p style="font-size:var(--text-xs); font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--color-text-muted); margin-bottom:var(--space-4);">Suivi</p>
        <?php
        $steps = [
            ['key'=>'draft',    'label'=>'Brouillon', 'sub'=>'Devis créé',        'icon'=>'✎'],
            ['key'=>'sent',     'label'=>'Envoyé',    'sub'=>'Email envoyé',       'icon'=>'✉'],
            ['key'=>'viewed',   'label'=>'Consulté',  'sub'=>'Client a ouvert',    'icon'=>'👁'],
            ['key'=>'accepted', 'label'=>'Accepté',   'sub'=>'Devis validé',       'icon'=>'✓'],
            ['key'=>'converted','label'=>'Facturé',   'sub'=>'Facture générée',    'icon'=>'€'],
        ];
        $statusOrder = array_column($steps,'key');
        $currentIdx  = array_search($quote['status'], $statusOrder);
        ?>
        <div class="timeline">
          <?php foreach ($steps as $si => $step):
            if ($step['key'] === 'draft' && $quote['status'] === 'refused') { $dotClass = 'done'; }
            elseif ($si < $currentIdx)  { $dotClass = 'done'; }
            elseif ($si === $currentIdx){ $dotClass = 'current'; }
            else                         { $dotClass = 'pending'; }
          ?>
          <div class="timeline-item">
            <div class="timeline-dot <?= $dotClass ?>"><?= $step['icon'] ?></div>
            <div class="timeline-text">
              <strong><?= $step['label'] ?></strong>
              <span><?= $step['sub'] ?></span>
            </div>
          </div>
          <?php endforeach; ?>
          <?php if ($quote['status'] === 'refused'): ?>
          <div class="timeline-item">
            <div class="timeline-dot current" style="background:#e53e3e;">✗</div>
            <div class="timeline-text"><strong>Refusé</strong><span>Devis décliné</span></div>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Notes internes -->
      <?php if ($quote['internal_notes']): ?>
      <div class="actions-card-section">
        <p style="font-size:var(--text-xs); font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:var(--color-text-muted); margin-bottom:var(--space-2);">Note interne</p>
        <p style="font-size:var(--text-sm); color:var(--color-text-muted); line-height:1.5;"><?= nl2br(htmlspecialchars($quote['internal_notes'])) ?></p>
      </div>
      <?php endif; ?>

    </div><!-- /actions-card -->
  </div>

</div><!-- /quote-detail-grid -->

<!-- Modal envoi email -->
<div id="sendModal" style="display:none; position:fixed; inset:0; z-index:1000; background:rgba(0,0,0,0.5); display:none; align-items:center; justify-content:center; padding:var(--space-4);">
  <div class="card" style="width:100%; max-width:520px; animation:slideUp .2s ease;">
    <div class="card-header">
      <h3 style="font-weight:700;">Envoyer le devis par email</h3>
      <button type="button" onclick="closeSendModal()" class="btn btn-ghost btn-sm" aria-label="Fermer">✕</button>
    </div>
    <form method="POST" action="/quotes/<?= $quote['id'] ?>/send" style="padding:var(--space-5);">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
      <div class="form-group">
        <label>Destinataire</label>
        <input type="email" name="to" class="form-control" value="<?= htmlspecialchars($quote['client_email'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label>Objet</label>
        <input type="text" name="subject" class="form-control"
               value="Devis <?= htmlspecialchars($quote['number']) ?> – <?= htmlspecialchars($quote['title']) ?>" required>
      </div>
      <div class="form-group">
        <label>Message</label>
        <textarea name="message" class="form-control" rows="5">Bonjour,

Veuillez trouver ci-joint notre devis <?= htmlspecialchars($quote['number']) ?> d'un montant de <?= number_format($quote['total_ttc'], 2, ',', ' ') ?> € TTC.

Ce devis est valable jusqu'au <?= date('d/m/Y', strtotime($quote['validity_date'])) ?>.

N'hésitez pas à nous contacter pour toute question.

Cordialement.</textarea>
      </div>
      <div style="display:flex; gap:var(--space-3); justify-content:flex-end;">
        <button type="button" onclick="closeSendModal()" class="btn btn-ghost">Annuler</button>
        <button type="submit" class="btn btn-primary">Envoyer</button>
      </div>
    </form>
  </div>
</div>

<script>
function openSendModal()  { document.getElementById('sendModal').style.display = 'flex'; }
function closeSendModal() { document.getElementById('sendModal').style.display = 'none'; }
document.getElementById('sendModal').addEventListener('click', e => {
  if (e.target === e.currentTarget) closeSendModal();
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSendModal(); });
</script>
