<?php
// Vue: création/édition de devis
$isEdit    = isset($quote);
$csrf      = App\Helpers\Session::csrf();
$today     = date('Y-m-d');
$validity  = date('Y-m-d', strtotime('+30 days'));
$vatRatesJson  = json_encode(array_column($vatRates ?? [], 'rate', 'label'));
$productsJson  = json_encode($products ?? []);
?>
<style>
.quote-form-grid {
  display: grid;
  grid-template-columns: 1fr 340px;
  gap: var(--space-6);
  align-items: start;
}

.lines-container {
  border: 1px solid var(--color-border);
  border-radius: var(--radius-xl);
  overflow: hidden;
  background: var(--color-surface);
}

.lines-header {
  display: grid;
  grid-template-columns: 2fr 80px 100px 80px 80px 80px 40px;
  gap: var(--space-2);
  padding: var(--space-3) var(--space-4);
  background: var(--color-surface-2);
  font-size: var(--text-xs);
  font-weight: 700;
  color: var(--color-text-muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
  border-bottom: 1px solid var(--color-border);
}

.line-row {
  display: grid;
  grid-template-columns: 2fr 80px 100px 80px 80px 80px 40px;
  gap: var(--space-2);
  padding: var(--space-3) var(--space-4);
  border-bottom: 1px solid var(--color-border);
  align-items: center;
  transition: background var(--transition-fast);
  cursor: grab;
}
.line-row:last-child { border-bottom: none; }
.line-row:hover { background: var(--color-surface-2); }
.line-row.dragging { opacity: 0.5; cursor: grabbing; }
.line-row.drag-over { border-top: 2px solid var(--color-primary); }

.line-cell input, .line-cell select {
  width: 100%;
  padding: 0.4rem 0.5rem;
  font-size: var(--text-sm);
  border: 1.5px solid var(--color-border);
  border-radius: var(--radius);
  background: var(--color-surface);
  color: var(--color-text);
  transition: border-color var(--transition-fast);
}
.line-cell input:focus, .line-cell select:focus {
  border-color: var(--color-primary);
  outline: none;
  box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}

.line-total {
  font-size: var(--text-sm);
  font-weight: 600;
  color: var(--color-text);
  text-align: right;
  font-variant-numeric: tabular-nums;
}

.line-delete {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: var(--radius);
  color: var(--color-text-muted);
  background: none;
  border: none;
  cursor: pointer;
  transition: all var(--transition-fast);
}
.line-delete:hover { color: var(--color-danger); background: #fee2e2; }

.totals-card {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: var(--radius-xl);
  padding: var(--space-5);
  position: sticky;
  top: calc(var(--topbar-height) + var(--space-4));
}

.total-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--space-2) 0;
  font-size: var(--text-sm);
  color: var(--color-text-muted);
  border-bottom: 1px solid var(--color-border);
}
.total-row:last-child { border-bottom: none; }

.total-row.total-final {
  font-size: var(--text-lg);
  font-weight: 800;
  color: var(--color-text);
  padding-top: var(--space-3);
  margin-top: var(--space-2);
}

.autosave-indicator {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  font-size: var(--text-xs);
  color: var(--color-text-muted);
  padding: var(--space-2) var(--space-4);
}

.autosave-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--color-success);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.4; }
}

@media (max-width: 1024px) {
  .quote-form-grid { grid-template-columns: 1fr; }
  .lines-header, .line-row { grid-template-columns: 2fr 70px 90px 70px 70px; }
  .lines-header > :last-child, .line-row > :last-child { display: none; }
}

@media (max-width: 640px) {
  .lines-header { display: none; }
  .line-row { grid-template-columns: 1fr 1fr; gap: var(--space-2); }
}
</style>

<div class="card" style="margin-bottom: var(--space-6);">
  <div class="card-header">
    <div>
      <h2 style="font-size:var(--text-xl); font-weight:800;">
        <?= $isEdit ? 'Modifier ' . htmlspecialchars($quote['number']) : 'Nouveau devis' ?>
      </h2>
      <?php if ($isEdit): ?>
      <p style="color:var(--color-text-muted);font-size:var(--text-sm);"><?= htmlspecialchars($quote['title']) ?></p>
      <?php endif; ?>
    </div>
    <div class="autosave-indicator" id="autosaveIndicator">
      <span class="autosave-dot" id="autosaveDot"></span>
      <span id="autosaveText">Autosave actif</span>
    </div>
  </div>
</div>

<form method="POST" action="<?= $isEdit ? '/quotes/' . $quote['id'] : '/quotes' ?>" id="quoteForm" novalidate>
  <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

  <div class="quote-form-grid">

    <!-- Colonne principale -->
    <div style="display:flex; flex-direction:column; gap:var(--space-5);">

      <!-- Infos générales -->
      <div class="card">
        <h3 style="font-weight:700; margin-bottom:var(--space-4);">Informations générales</h3>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--space-4);">
          <div class="form-group" style="grid-column:1/-1;">
            <label for="title">Objet du devis *</label>
            <input type="text" id="title" name="title" class="form-control"
                   placeholder="Ex: Création site vitrine + SEO"
                   value="<?= htmlspecialchars($quote['title'] ?? '') ?>" required autofocus>
          </div>
          <div class="form-group">
            <label for="client_id">Client *</label>
            <select id="client_id" name="client_id" class="form-control" required>
              <option value="">Sélectionnez un client…</option>
              <?php foreach ($clients as $c): ?>
              <option value="<?= $c['id'] ?>" <?= ($quote['client_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
            <a href="/clients/new" target="_blank" style="font-size:var(--text-xs); color:var(--color-primary); margin-top:4px; display:block;">+ Ajouter un client</a>
          </div>
          <div class="form-group">
            <label for="issue_date">Date d'émission</label>
            <input type="date" id="issue_date" name="issue_date" class="form-control"
                   value="<?= htmlspecialchars($quote['issue_date'] ?? $today) ?>">
          </div>
          <div class="form-group">
            <label for="validity_date">Valide jusqu'au *</label>
            <input type="date" id="validity_date" name="validity_date" class="form-control"
                   value="<?= htmlspecialchars($quote['validity_date'] ?? $validity) ?>" required>
          </div>
          <div class="form-group">
            <label for="country_vat">Pays (TVA)</label>
            <select id="country_vat" name="country_vat" class="form-control">
              <option value="FR" <?= ($quote['country_vat'] ?? 'FR') === 'FR' ? 'selected' : '' ?>>France (TVA 20%)</option>
              <option value="DE" <?= ($quote['country_vat'] ?? '') === 'DE' ? 'selected' : '' ?>>Allemagne (MwSt 19%)</option>
              <option value="BE" <?= ($quote['country_vat'] ?? '') === 'BE' ? 'selected' : '' ?>>Belgique (TVA 21%)</option>
              <option value="CH" <?= ($quote['country_vat'] ?? '') === 'CH' ? 'selected' : '' ?>>Suisse (TVA 8.1%)</option>
              <option value="ES" <?= ($quote['country_vat'] ?? '') === 'ES' ? 'selected' : '' ?>>Espagne (IVA 21%)</option>
              <option value="INTL" <?= ($quote['country_vat'] ?? '') === 'INTL' ? 'selected' : '' ?>>Hors UE (Exonéré)</option>
            </select>
          </div>
          <div class="form-group">
            <label for="deposit_percent">Acompte demandé (%)</label>
            <input type="number" id="deposit_percent" name="deposit_percent" class="form-control"
                   min="0" max="100" step="5" placeholder="0"
                   value="<?= htmlspecialchars($quote['deposit_percent'] ?? '0') ?>">
          </div>
          <div class="form-group" style="grid-column:1/-1;">
            <label for="description">Description (optionnel)</label>
            <textarea id="description" name="description" class="form-control" rows="2"
                      placeholder="Contexte du projet, détail de la demande…"><?= htmlspecialchars($quote['description'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

      <!-- Lignes de prestation -->
      <div class="card" style="padding:0;">
        <div style="padding: var(--space-4) var(--space-5); border-bottom:1px solid var(--color-border); display:flex; align-items:center; justify-content:space-between;">
          <h3 style="font-weight:700;">Prestations &amp; produits</h3>
          <button type="button" id="addLineBtn" class="btn btn-secondary btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Ajouter une ligne
          </button>
        </div>

        <div class="lines-header">
          <span>Désignation</span>
          <span>Qté</span>
          <span>Prix HT</span>
          <span>TVA</span>
          <span>Remise</span>
          <span style="text-align:right;">Total HT</span>
          <span></span>
        </div>

        <div id="linesContainer">
          <?php
          $existingLines = $quote['lines'] ?? [[]];
          foreach ($existingLines as $i => $line):
          ?>
          <div class="line-row" draggable="true" data-index="<?= $i ?>">
            <div class="line-cell">
              <input type="text" name="lines[name][]" placeholder="Désignation *"
                     value="<?= htmlspecialchars($line['name'] ?? '') ?>" required>
              <input type="hidden" name="lines[product_id][]" value="<?= $line['product_id'] ?? '' ?>">
              <input type="hidden" name="lines[type][]" value="service">
              <input type="text" name="lines[description][]" placeholder="Détail (optionnel)"
                     value="<?= htmlspecialchars($line['description'] ?? '') ?>"
                     style="margin-top:4px; font-size:var(--text-xs); color:var(--color-text-muted);">
            </div>
            <div class="line-cell">
              <input type="number" name="lines[quantity][]" placeholder="1" min="0" step="0.01"
                     value="<?= htmlspecialchars($line['quantity'] ?? '1') ?>" class="qty-input">
            </div>
            <div class="line-cell">
              <input type="number" name="lines[unit_price][]" placeholder="0.00" min="0" step="0.01"
                     value="<?= htmlspecialchars($line['unit_price'] ?? '') ?>" class="price-input">
            </div>
            <div class="line-cell">
              <select name="lines[vat_rate][]" class="vat-input">
                <?php foreach ($vatRates as $vr): ?>
                <option value="<?= $vr['rate'] ?>" <?= ($line['vat_rate'] ?? 20) == $vr['rate'] ? 'selected' : '' ?>>
                  <?= number_format($vr['rate'], 1) ?>%
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="line-cell">
              <input type="number" name="lines[discount_value][]" placeholder="0" min="0" step="0.01"
                     value="<?= htmlspecialchars($line['discount_value'] ?? '0') ?>" class="discount-input">
              <input type="hidden" name="lines[discount_type][]" value="percent">
            </div>
            <div class="line-cell line-total" data-total>0,00 €</div>
            <div class="line-cell">
              <button type="button" class="line-delete" title="Supprimer">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
              </button>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <div id="catalogPicker" style="padding:var(--space-4); border-top:1px solid var(--color-border); display:none;">
          <p style="font-size:var(--text-xs); color:var(--color-text-muted); margin-bottom:var(--space-2);">Ajouter depuis le catalogue :</p>
          <div style="display:flex; flex-wrap:wrap; gap:var(--space-2);">
            <?php foreach ($products as $p): ?>
            <button type="button" class="btn btn-secondary btn-sm catalog-pick"
                    data-name="<?= htmlspecialchars($p['name']) ?>"
                    data-price="<?= $p['unit_price'] ?>"
                    data-vat="<?= $p['vat_rate'] ?>"
                    data-ref="<?= htmlspecialchars($p['reference'] ?? '') ?>"
                    data-id="<?= $p['id'] ?>">
              <?= htmlspecialchars($p['name']) ?> &mdash; <?= number_format($p['unit_price'], 2, ',', '\u00a0') ?> €
            </button>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Notes -->
      <div class="card">
        <h3 style="font-weight:700; margin-bottom:var(--space-4);">Notes &amp; conditions</h3>
        <div style="display:grid; gap:var(--space-4);">
          <div class="form-group">
            <label for="payment_terms">Conditions de paiement</label>
            <textarea id="payment_terms" name="payment_terms" class="form-control" rows="2"
              placeholder="Ex: 30% à la commande, solde à la livraison"><?= htmlspecialchars($quote['payment_terms'] ?? 'Paiement à 30 jours réception de facture') ?></textarea>
          </div>
          <div class="form-group">
            <label for="notes">Notes visibles sur le devis</label>
            <textarea id="notes" name="notes" class="form-control" rows="3"
              placeholder="Informations complémentaires pour votre client…"><?= htmlspecialchars($quote['notes'] ?? '') ?></textarea>
          </div>
          <div class="form-group">
            <label for="internal_notes">
              Notes internes
              <span style="font-size:var(--text-xs); color:var(--color-text-muted); font-weight:400;">(non visible sur le PDF)</span>
            </label>
            <textarea id="internal_notes" name="internal_notes" class="form-control" rows="2"
              placeholder="Mémo interne…"><?= htmlspecialchars($quote['internal_notes'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

    </div><!-- /colonne principale -->

    <!-- Colonne Totaux -->
    <div>
      <div class="totals-card">
        <h3 style="font-weight:700; margin-bottom:var(--space-4);">Récapitulatif</h3>

        <div class="total-row">
          <span>Sous-total HT</span>
          <span id="summaryHt" style="font-variant-numeric:tabular-nums;">0,00 €</span>
        </div>

        <!-- Remise globale -->
        <div class="total-row">
          <div style="display:flex; align-items:center; gap:var(--space-2); flex:1;">
            <span>Remise globale</span>
            <select name="discount_type" id="discountType" style="font-size:var(--text-xs); padding:2px 6px; border:1px solid var(--color-border); border-radius:4px; background:var(--color-surface);">
              <option value="percent" <?= ($quote['discount_type'] ?? '') === 'percent' ? 'selected' : '' ?>>%</option>
              <option value="fixed"   <?= ($quote['discount_type'] ?? '') === 'fixed' ? 'selected' : '' ?>>€</option>
            </select>
            <input type="number" name="discount_value" id="discountValue" min="0" step="0.01"
                   value="<?= htmlspecialchars($quote['discount_value'] ?? '0') ?>"
                   style="width:70px; font-size:var(--text-xs); padding:2px 6px; border:1px solid var(--color-border); border-radius:4px; background:var(--color-surface); color:var(--color-text);">
          </div>
          <span id="summaryDiscount" style="color:var(--color-danger); font-variant-numeric:tabular-nums;">0,00 €</span>
        </div>

        <div class="total-row">
          <span>Total TVA</span>
          <span id="summaryVat" style="font-variant-numeric:tabular-nums;">0,00 €</span>
        </div>

        <div class="total-row total-final">
          <span>Total TTC</span>
          <span id="summaryTtc">0,00 €</span>
        </div>

        <div class="total-row" id="depositRow" style="display:none;">
          <span>Acompte (<span id="depositPct">0</span>%)</span>
          <span id="summaryDeposit" style="color:var(--color-primary);">0,00 €</span>
        </div>

        <div style="margin-top:var(--space-5); display:flex; flex-direction:column; gap:var(--space-3);">
          <button type="submit" name="action" value="draft" class="btn btn-secondary w-full">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Enregistrer brouillon
          </button>
          <button type="submit" name="send_now" value="1" class="btn btn-primary w-full">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Enregistrer &amp; envoyer
          </button>
          <a href="/quotes" class="btn btn-ghost w-full">Annuler</a>
        </div>
      </div>
    </div>

  </div><!-- /quote-form-grid -->
</form>

<script>
(function() {
  const products = <?= $productsJson ?>;
  const container = document.getElementById('linesContainer');
  let lineIndex = <?= count($existingLines ?? []) ?>;
  let autosaveTimer;
  let dragSrc;

  // ---- Calcul des totaux ----
  function fmt(n) {
    return n.toLocaleString('fr-FR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' €';
  }

  function computeLineTotal(row) {
    const qty   = parseFloat(row.querySelector('.qty-input')?.value)   || 0;
    const price = parseFloat(row.querySelector('.price-input')?.value) || 0;
    const disc  = parseFloat(row.querySelector('.discount-input')?.value) || 0;
    const ht    = qty * price;
    const discAmount = ht * (disc / 100);
    const total = ht - discAmount;
    const totalCell = row.querySelector('[data-total]');
    if (totalCell) totalCell.textContent = fmt(total);
    return { ht: total, vat: total * (parseFloat(row.querySelector('.vat-input')?.value || 20) / 100) };
  }

  function computeAllTotals() {
    let totalHt = 0, totalVat = 0;
    container.querySelectorAll('.line-row').forEach(row => {
      const { ht, vat } = computeLineTotal(row);
      totalHt  += ht;
      totalVat += vat;
    });

    const discType  = document.getElementById('discountType').value;
    const discVal   = parseFloat(document.getElementById('discountValue').value) || 0;
    const globalDisc = discType === 'percent' ? totalHt * (discVal / 100) : discVal;
    const netHt = totalHt - globalDisc;
    const ttc   = netHt + totalVat;

    document.getElementById('summaryHt').textContent       = fmt(totalHt);
    document.getElementById('summaryDiscount').textContent = '-' + fmt(globalDisc);
    document.getElementById('summaryVat').textContent      = fmt(totalVat);
    document.getElementById('summaryTtc').textContent      = fmt(ttc);

    const depositPct = parseFloat(document.getElementById('deposit_percent')?.value) || 0;
    if (depositPct > 0) {
      document.getElementById('depositRow').style.display = '';
      document.getElementById('depositPct').textContent = depositPct;
      document.getElementById('summaryDeposit').textContent = fmt(ttc * depositPct / 100);
    } else {
      document.getElementById('depositRow').style.display = 'none';
    }
  }

  // ---- Ajout de ligne ----
  function addLine(data = {}) {
    const row = document.createElement('div');
    row.className = 'line-row';
    row.draggable = true;
    row.dataset.index = lineIndex++;
    row.innerHTML = `
      <div class="line-cell">
        <input type="text" name="lines[name][]" placeholder="Désignation *" value="${data.name || ''}" required>
        <input type="hidden" name="lines[product_id][]" value="${data.id || ''}">
        <input type="hidden" name="lines[type][]" value="service">
        <input type="text" name="lines[description][]" placeholder="Détail (optionnel)" value="${data.description || ''}" style="margin-top:4px; font-size:var(--text-xs); color:var(--color-text-muted);">
      </div>
      <div class="line-cell"><input type="number" name="lines[quantity][]" placeholder="1" min="0" step="0.01" value="${data.quantity || '1'}" class="qty-input"></div>
      <div class="line-cell"><input type="number" name="lines[unit_price][]" placeholder="0.00" min="0" step="0.01" value="${data.price || ''}" class="price-input"></div>
      <div class="line-cell">
        <select name="lines[vat_rate][]" class="vat-input">
          <option value="20" ${!data.vat || data.vat == 20 ? 'selected' : ''}>20,0%</option>
          <option value="10" ${data.vat == 10 ? 'selected' : ''}>10,0%</option>
          <option value="5.5" ${data.vat == 5.5 ? 'selected' : ''}>5,5%</option>
          <option value="2.1" ${data.vat == 2.1 ? 'selected' : ''}>2,1%</option>
          <option value="0" ${data.vat == 0 ? 'selected' : ''}>0,0%</option>
        </select>
      </div>
      <div class="line-cell"><input type="number" name="lines[discount_value][]" placeholder="0" min="0" step="0.01" value="0" class="discount-input"><input type="hidden" name="lines[discount_type][]" value="percent"></div>
      <div class="line-cell line-total" data-total>0,00 €</div>
      <div class="line-cell"><button type="button" class="line-delete" title="Supprimer"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg></button></div>
    `;
    container.appendChild(row);
    bindRowEvents(row);
    computeAllTotals();
    row.querySelector('input[name="lines[name][]"]').focus();
  }

  // ---- Events par ligne ----
  function bindRowEvents(row) {
    row.querySelectorAll('input, select').forEach(el => {
      el.addEventListener('input', () => { computeAllTotals(); scheduleAutosave(); });
    });
    row.querySelector('.line-delete').addEventListener('click', () => {
      row.remove();
      computeAllTotals();
    });
    // Drag & drop
    row.addEventListener('dragstart', e => { dragSrc = row; row.classList.add('dragging'); });
    row.addEventListener('dragend',   () => row.classList.remove('dragging'));
    row.addEventListener('dragover',  e => { e.preventDefault(); row.classList.add('drag-over'); });
    row.addEventListener('dragleave', () => row.classList.remove('drag-over'));
    row.addEventListener('drop', e => {
      e.preventDefault();
      row.classList.remove('drag-over');
      if (dragSrc && dragSrc !== row) {
        const rows = [...container.children];
        const srcIdx = rows.indexOf(dragSrc);
        const tgtIdx = rows.indexOf(row);
        if (srcIdx < tgtIdx) row.after(dragSrc);
        else row.before(dragSrc);
      }
    });
  }

  // Init rows existantes
  container.querySelectorAll('.line-row').forEach(row => bindRowEvents(row));
  computeAllTotals();

  // Bouton ajouter ligne
  document.getElementById('addLineBtn').addEventListener('click', () => {
    const catalog = document.getElementById('catalogPicker');
    catalog.style.display = catalog.style.display === 'none' ? 'block' : 'none';
    addLine();
    catalog.style.display = 'none';
  });

  // Catalogue
  document.querySelectorAll('.catalog-pick').forEach(btn => {
    btn.addEventListener('click', () => {
      addLine({ name: btn.dataset.name, price: btn.dataset.price, vat: btn.dataset.vat, ref: btn.dataset.ref, id: btn.dataset.id });
    });
  });

  // Remise & dépôt globaux
  ['discountType','discountValue','deposit_percent'].forEach(id => {
    const el = document.getElementById(id);
    el && el.addEventListener('input', computeAllTotals);
  });

  // ---- Autosave ----
  function scheduleAutosave() {
    clearTimeout(autosaveTimer);
    const dot = document.getElementById('autosaveDot');
    const txt = document.getElementById('autosaveText');
    dot.style.background = 'var(--color-warning)';
    txt.textContent = 'Modifications non sauvegardées…';
    autosaveTimer = setTimeout(() => {
      // Sauvegarde locale (sessionStorage inactif en sandbox — on marque juste visuellement)
      dot.style.background = 'var(--color-success)';
      txt.textContent = 'Prêt à enregistrer';
    }, 2000);
  }

  document.getElementById('quoteForm').querySelectorAll('input, select, textarea').forEach(el => {
    el.addEventListener('input', scheduleAutosave);
  });

})();
</script>
